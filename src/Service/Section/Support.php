<?php


namespace App\Service\Section;


use App\Dto\Support as SupportDto;
use App\Entity\LastBotQuestion;
use App\Entity\Support as SupportEntity;
use App\Entity\User;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class Support extends Base implements SupportInterface
{
    function start(?string $additional_text_to_header = null): void
    {
        $this->clearLastBotQuestion();

        if ($this->getUser()->isAdministrator()) {
            $questions = $this->supportRepository->getListUnanswered();

            if ($questions->count() === 0) {
                $text = '💬 Сообщений на данный момент нет';

                if (!empty($additional_text_to_header)) {
                    $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
                }

                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Закрыть',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAIN_MENU
                        ])
                    ]);
            } else {
                $text = '💬 Ваш ответ ожидают:';

                if (!empty($additional_text_to_header)) {
                    $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
                }

                $keyboard = (new Keyboard())->inline();

                $i = 0;
                /**
                 * @var SupportEntity $question
                 */
                foreach ($questions as $question) {
                    $i++;
                    $keyboard
                        ->row([
                            'text' => '👤 '.$question->getUser()->getName().' - Место в очереди '.$i,
                            'callback_data' => json_encode([
                                'c' => self::COMMAND_SUPPORT_ADMIN_QUESTION,
                                'id' => $question->getId()
                            ])
                        ]);
                }

                $keyboard
                    ->row([
                        'text' => 'Закрыть',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAIN_MENU
                        ])
                    ]);
            }
        } else {
            $this->getLastBotQuestion()
                ->setType(LastBotQuestion::TYPE_SUPPORT_USER_QUESTION)
            ;
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

            $text = '📲 Служба поддержки'.PHP_EOL.PHP_EOL.'Задайте вопрос и в скором времени получите ответ';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
        }

        $delete_user_answer = false;
        if (!empty($additional_text_to_header)) {
            $delete_user_answer = true;
        }

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function question(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()
            ->addAnswer('question_id', $id)
            ->setType(LastBotQuestion::TYPE_SUPPORT_ADMIN_ANSWER)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $question = $this->supportRepository->findById($id);

        $text = '👤 '.$question->getUser()->getName().PHP_EOL.PHP_EOL.'💬 Сообщения:'.PHP_EOL;

        foreach ($question->getQuestions() as $q) {
            $text .= $q.PHP_EOL;
        }

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SUPPORT
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleAdminAnswerOnAnswerQuestion(): void
    {
        $text = $this->getText();
        $id = (int) $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['question_id'];

        if (empty($text)) {
            $text = '⚠️ Вы прислали что-то не то. Пришлите текст.';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Отмена',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SUPPORT
                    ])
                ]);
            $this->sendMessage($text, $keyboard, true);
        } else {
            $support = $this->supportRepository->findById($id);

            $support
                ->setAnswer($text)
                ->setAnswered(true)
                ->setAdministrator($this->getUser())
            ;

            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_DELETE_MESSAGE
                    ])
                ]);

            try {
                $this->api->sendMessage([
                    'chat_id' => $support->getUser()->getChatId(),
                    'text' => $text,
                    'reply_markup' => $keyboard
                ]);
            } catch (TelegramSDKException $e) {
                $this->logger->critical($e->getMessage());
                die();
            }

            $this->supportRepository->save($support);

            $this->start('✅ Ваш ответ успешно отправлен!');
        }
    }

    function handleUserAnswerOnAskQuestion(): void
    {
        $text = $this->getText();

        if (empty($text)) {
            $text = '⚠️ Вы прислали что-то не то. Пришлите текст.';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Отмена',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
        } else {
            $support = $this->supportRepository->findUnansweredByUserId($this->getUser()->getId());

            if (empty($support)) {
                $dto = new SupportDto($this->getUser(), $text);
                $support = SupportEntity::create($dto);
            } else {
                $support->addQuestion($text);
            }

            $this->supportRepository->save($support);

            $admins = $this->userRepository->getAdminsList();

            /**
             * @var User $admin
             */
            foreach ($admins as $admin) {
                $text = '💬 '.$this->getUser()->getName().' - отправил сообщение в тех поддержку и ожидает ответа';
                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Закрыть',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_DELETE_MESSAGE
                        ])
                    ]);

                try {
                    $this->api->sendMessage([
                        'chat_id' => $admin->getChatId(),
                        'text' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e);
                }
            }


            $text = '✅ Ваше сообщение успешно отправлено! Ожидайте ответа или можете написать что-то еще';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
        }

        $this->sendMessage($text, $keyboard, true);
    }
}