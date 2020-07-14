<?php


namespace App\Service\Section;


use App\Dto\Promocode;
use App\Entity\Item;
use App\Entity\LastBotQuestion;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class Mailing extends Base implements MailingInterface
{
    function start(): void
    {
        $this->clearLastBotQuestion();

        $this->getLastBotQuestion()
            ->setType(LastBotQuestion::TYPE_MAILING_TEXT)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Отправьте боту текст, что хотите разослать:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function removeText(): void
    {
        $this->getLastBotQuestion()
            ->unsetAnswer('text')
            ->setType(LastBotQuestion::TYPE_MAILING_TEXT)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $text = '💬 Отправьте боту текст, что хотите разослать:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function file(): void
    {
        $this->getLastBotQuestion()->setType(LastBotQuestion::TYPE_MAILING_FILE);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = 'Пришлите медиафайл';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function menu(bool $delete_user_answer = false): void
    {
        $text = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['text'];
        $item_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['item_id'] ?? null;
        $buttons = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['buttons'] ?? null;
        $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'] ?? null;
        $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'] ?? null;

        $keyboard = (new Keyboard())->inline();

        if (empty($file_id) && empty($file_type)) {
            $keyboard
                ->row([
                    'text' => 'Прикрепить файл',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_FILE
                    ])
                ]);
        } else {
            $keyboard
                ->row([
                    'text' => 'Удалить медиафайл',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_REMOVE_FILE
                    ])
                ]);
        }

        if (empty($item_id)) {
            $keyboard
                ->row([
                    'text' => 'Добавить курс',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_COURSES
                    ])
                ]);
        } else {
            $item = $this->itemRepository->findById($item_id);

            $keyboard
                ->row([
                    'text' => '✅ '.$item->getName(),
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_REMOVE_COURSE,
                        'id' => $item->getId()
                    ])
                ]);
        }

        if (!empty($buttons)) {
            foreach ($buttons as $button) {
                $keyboard
                    ->row([
                        'text' => $button['name'],
                        'url' => $button['url']
                    ]);
            }
        }

        $keyboard
            ->row([
                'text' => 'Добавить кнопки',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_BUTTONS
                ])
            ]);

        $keyboard
            ->row([
                'text' => 'Удалить текст',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_REMOVE_TEXT
                ])
            ])
            ->row([
                'text' => 'Продолжить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_WHOM
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING
                ])
            ]);

        if (!empty($file_id) && !empty($file_type)) {

            try {
                $this->api->deleteMessage([
                    'chat_id' => $this->getChatId(),
                    'message_id' => $this->getLastBotAction()->getMessageId()
                ]);
            } catch (TelegramSDKException $e) {
                $this->logger->critical($e->getMessage());
                die();
            }

            switch ($file_type) {
                case BaseAbstract::FILE_TYPE_PHOTO:
                    try {
                        $message = $this->api->sendPhoto([
                            'chat_id' => $this->getChatId(),
                            'photo' => $file_id,
                            'caption' => $text,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        die();
                    }
                    break;
                case BaseAbstract::FILE_TYPE_VIDEO:
                    try {
                        $message = $this->api->sendVideo([
                            'chat_id' => $this->getChatId(),
                            'video' => $file_id,
                            'caption' => $text,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        die();
                    }
                    break;
                case BaseAbstract::FILE_TYPE_DOCUMENT:
                    try {
                        $message = $this->api->sendVideo([
                            'chat_id' => $this->getChatId(),
                            'document' => $file_id,
                            'caption' => $text,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        die();
                    }
                    break;
            }

            $this->getLastBotAction()->setMessageId($message->messageId);
            $this->lastBotActionRepository->save($this->getLastBotAction());
            $this->deleteMessage();
        } else {
            $this->sendMessage($text, $keyboard, $delete_user_answer);
        }
    }

    function courses(): void
    {
        $page = $this->getCallbackData()->p ?? 1;

        $limit = 5;
        $items = $this->itemRepository->getList($page, $limit);
        $pages = ceil($items->count() / $limit);

        $text = 'Выберите курс:';
        $keyboard = (new Keyboard())->inline();

        if ($items->count() > 0) {

            /**
             * @var Item $item
             */
            foreach ($items as $item) {
                $keyboard
                    ->row([
                        'text' => '✅ '.$item->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_COURSE,
                            'id' => $item->getId()
                        ])
                    ]);
            }

            if ($pages > 1) {

                $previous_page = $page - 1;
                if ($previous_page < 1) {
                    $previous_page = $pages;
                }

                $next_page = $page + 1;
                if ($next_page > $pages) {
                    $next_page = 1;
                }

                $keyboard
                    ->row([
                        'text' => '◀️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_COURSES,
                            'p' => $previous_page
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_COURSES,
                            'p' => $next_page
                        ])
                    ]);
            }
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function course(): void
    {
        $id = $this->getCallbackData()->id;
        $this->getLastBotQuestion()->addAnswer('item_id', $id);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->menu();
    }

    function removeCourse(): void
    {
        $this->getLastBotQuestion()->unsetAnswer('item_id');
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->menu();
    }

    function buttons(): void
    {
        $this->getLastBotQuestion()->setType(LastBotQuestion::TYPE_MAILING_BUTTONS);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = 'Отправьте мне список URL-кнопок в одном сообщении. Пожалуйста, следуйте этому формату:'.PHP_EOL.PHP_EOL;
        $text .= 'Кнопка1 - http://example1.com'.PHP_EOL.'Кнопка2 - http://example2.com'.PHP_EOL.PHP_EOL;
        $text .= '(в названиях кнопок могут использоваться смайлики)';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnButtons(): void
    {
        $buttons = $this->getText();

        if (empty($buttons)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите текст:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
            $this->sendMessage($text, $keyboard, true);
        } else {

            $count = preg_match_all('/.+ - .+/', $buttons, $matches);

            $buttons = [];
            $is_buttons_valid = true;
            if ($count !== 0) {
                foreach ($matches[0] as $button) {
                    $name_url = explode(' - ', $button);
                    $buttons[] = ['name' => $name_url[0], 'url' => $name_url[1]];

                    if (filter_var($name_url[1], FILTER_VALIDATE_URL) === false) {
                        $is_buttons_valid = false;
                        break;
                    }
                }
            } else {
                $is_buttons_valid = false;
            }

            if (!$is_buttons_valid) {
                $text = '⚠️ Некорректный формат кнопок! Пожалуйста, следуйте этому формату:'.PHP_EOL.PHP_EOL;
                $text .= 'Кнопка1 - http://example1.com'.PHP_EOL.'Кнопка2 - http://example2.com'.PHP_EOL.PHP_EOL;
                $text .= '(в названиях кнопок могут использоваться смайлики)';
                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Назад',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAIN_MENU
                        ])
                    ]);
                $this->sendMessage($text, $keyboard, true);
            } else {
                $previous_buttons = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['buttons'] ?? [];
                $this->getLastBotQuestion()->addAnswer('buttons', array_merge($previous_buttons, $buttons));
                $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
                $this->menu(true);
            }
        }
    }

    function handleUserAnswerOnText(): void
    {
        $text = $this->getText();

        if (empty($text)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите текст:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
            $this->sendMessage($text, $keyboard, true);
        } else {
            $this->getLastBotQuestion()->addAnswer('text', $text);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
            $this->menu(true);
        }
    }

    function handleUserAnswerOnFile(): void
    {
        $document = $this->getWebhookUpdate()->getMessage()->get('document');
        $video = $this->getWebhookUpdate()->getMessage()->get('video');
        $photo = $this->getWebhookUpdate()->getMessage()->get('photo');

        $file_id = null;
        $file_type = null;
        $correct_mime_types = ['image/gif', 'image/jpeg', 'image/png', 'video/mpeg', 'video/mp4', 'video/webm', 'video/x-flv'];

        $is_correct_mime_type = false;
        if (!empty($document)) {
            $file_id = $document->get('file_id');
            $file_type = BaseAbstract::FILE_TYPE_DOCUMENT;
            $mime_type = $document->get('mime_type');

            if (in_array($mime_type, $correct_mime_types)) {
                $is_correct_mime_type = true;
            }
        }

        if (!empty($video)) {
            $file_id = $video->get('file_id');
            $file_type = BaseAbstract::FILE_TYPE_VIDEO;
            $mime_type = $video->get('mime_type');

            if (in_array($mime_type, $correct_mime_types)) {
                $is_correct_mime_type = true;
            }
        }

        if (!empty($photo)) {
            $file_id = $photo->get('file_id');
            $file_type = BaseAbstract::FILE_TYPE_PHOTO;
            $is_correct_mime_type = true;
        }

        if (empty($file_id) && empty($file_type)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите файл:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_MENU
                    ])
                ]);
            $this->sendMessage($text, $keyboard, true);
        } elseif (!$is_correct_mime_type) {
            $text = '⚠️ Поддерживаемые типы: фото, видео или GIF';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_MENU
                    ])
                ]);
            $this->sendMessage($text, $keyboard, true);
        } else {
            $this->getLastBotQuestion()
                ->addAnswer('file_id', $file_id)
                ->addAnswer('file_type', $file_type)
            ;
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
            $this->menu(true);
        }
    }

    function removeFile(): void
    {
        $this->getLastBotQuestion()
            ->unsetAnswer('file_id')
            ->unsetAnswer('file_type')
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->menu();
    }
}