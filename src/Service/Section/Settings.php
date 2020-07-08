<?php


namespace App\Service\Section;


use App\Dto\Item as ItemDto;
use App\Dto\User as UserDto;
use App\Entity\Item;
use App\Entity\LastBotQuestion;
use App\Entity\User;
use Telegram\Bot\Keyboard\Keyboard;

class Settings extends Base implements SettingsInterface
{
    function start(?string $additional_text_to_header = null): void
    {
        $this->clearLastBotQuestion();

        $text = '⚙ Настройки:';
        if (!empty($additional_text_to_header)) {
            $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
        }
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '📖 Добавить курс',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE
                ])
            ])
            ->row([
                'text'=> '👥 Администраторы',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADMINS_LIST
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function addCourse(): void
    {
        $this->getLastBotQuestion()->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_NAME);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Впишите название курса';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnAddCourseName(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
            $delete_user_answer = false;
        } else {
            $name = $this->getText();
        }

        $this->getLastBotQuestion()
            ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_CATEGORY)
            ->addAnswer('name', $name)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Выберите или создайте категорию:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Пропустить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE
                ])
            ]);

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function addCourseSkipCategory(): void
    {
        $this->getLastBotQuestion()
            ->addAnswer('category', null)
            ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите текст:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                    'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_NAME
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnAddCourseText(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $text = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['text'];
            $delete_user_answer = false;
        } else {
            $text = $this->getText();
        }

        $this->getLastBotQuestion()
            ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_FILE)
            ->addAnswer('text', $text)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Прикрепите файл:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY
                ])
            ]);

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function handleUserAnswerOnAddCourseFile(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'];
            $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'];
            $delete_user_answer = false;
        } else {
            $document = $this->getWebhookUpdate()->getMessage()->get('document');
            $video = $this->getWebhookUpdate()->getMessage()->get('video');
            $photo = $this->getWebhookUpdate()->getMessage()->get('photo');

            $file_id = null;
            $file_type = null;

            if (!empty($document)) {
                $file_id = $document->get('file_id');
                $file_type = Item::FILE_TYPE_DOCUMENT;
            }

            if (!empty($video)) {
                $file_id = $video->get('file_id');
                $file_type = Item::FILE_TYPE_VIDEO;
            }

            if (!empty($photo)) {
                $file_id = $photo->get('file_id');
                $file_type = Item::FILE_TYPE_PHOTO;
            }
        }

        if (empty($file_id) && empty($file_type)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите файл:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY
                    ])
                ]);
        } else {

            $this->getLastBotQuestion()
                ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_ABOUT_URL)
                ->addAnswer('file_id', $file_id)
                ->addAnswer('file_type', $file_type);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

            $text = '💬 Введите ссылку на описание:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                        'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT
                    ])
                ]);
        }

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function handleUserAnswerOnAddCourseAboutUrl(): void
    {
        $this->getLastBotQuestion()->addAnswer('about_url', $this->getText());
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Выберите видимость курса:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Видимый',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SET_VISIBILITY,
                    'v' => true
                ])
            ], [
                'text' => 'Невидимый',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SET_VISIBILITY,
                    'v' => false
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                    'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT
                ])
            ]);

        $this->sendMessage($text, $keyboard, true);
    }

    function addCourseSetVisibility(): void
    {
        $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
        $category = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category'];
        $text = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['text'];
        $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'];
        $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'];
        $about_url = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['about_url'];
        $visible = json_decode($this->getWebhookUpdate()->get('callback_query')->get('data'))->v;

        $dto = new ItemDto($name, $category, $text, $file_id, $file_type, $about_url, $visible);
        $item = Item::create($dto);
        $this->itemRepository->save($item);

        $this->start('✅ Курс успешно добавлен!');
    }

    function adminsList(): void
    {
        $text = '👥 Администраторы:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '➕ Добавить администратора ➕',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_ADMIN
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function addAdmin(): void
    {
        $this->getLastBotQuestion()->setType(LastBotQuestion::TYPE_SETTINGS_ADD_ADMIN_NAME);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите имя администратора:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADMINS_LIST
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnAddAdminName(): void
    {
        $this->getLastBotQuestion()
            ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_ADMIN_CHAT_ID)
            ->addAnswer('name', $this->getText())
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите ChatID:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_ADMIN
                ])
            ]);

        $this->sendMessage($text, $keyboard, true);
    }

    function handleUserAnswerOnAddAdminChatId(): void
    {
        $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
        $chat_id = (int) $this->getText();

        $dto = new UserDto($name, $chat_id, true);
        $user = User::create($dto);
        $this->userRepository->save($user);

        $this->start('✅ Администратор '.$name.' успешно добавлен!');
    }


}
