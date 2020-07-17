<?php


namespace App\Service\Section;


use App\Entity\Item;
use App\Entity\LastBotQuestion;
use App\Entity\Promocode;
use App\Entity\User;
use DateTime;
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
        $this->getLastBotQuestion()->unsetAnswer('whom_all');
        $this->getLastBotQuestion()->unsetAnswer('whom_course_id');
        $this->getLastBotQuestion()->unsetAnswer('whom_promocode_id');

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
                'text' => 'Заменить текст',
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
                case BaseAbstract::FILE_TYPE_ANIMATION:
                    try {
                        $message = $this->api->sendAnimation([
                            'chat_id' => $this->getChatId(),
                            'animation' => $file_id,
                            'caption' => $text,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        die();
                    }
                    break;
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
                            'animation' => true,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        die();
                    }
                    break;
                case BaseAbstract::FILE_TYPE_DOCUMENT:
                    try {
                        $message = $this->api->sendDocument([
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

    function courses(
        int $cmd_select = self::COMMAND_MAILING_COURSE,
        int $cmd_list = self::COMMAND_MAILING_COURSES,
        int $cmd_back = self::COMMAND_MAILING_MENU
    ): void {
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
                            'c' => $cmd_select,
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
                            'c' => $cmd_list,
                            'p' => $previous_page
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => $cmd_list,
                            'p' => $next_page
                        ])
                    ]);
            }
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => $cmd_back
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
                        'c' => self::COMMAND_MAILING_MENU
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
                            'c' => self::COMMAND_MAILING_MENU
                        ])
                    ]);
                $this->sendMessage($text, $keyboard, true);
            } else {
                $this->getLastBotQuestion()->addAnswer('buttons', $buttons);
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
        $correct_mime_types = ['video/mov', 'image/gif', 'image/jpg', 'image/jpeg', 'image/png', 'video/mpeg', 'video/mp4', 'video/webm', 'video/x-flv'];

        $mime_type = null;
        $is_correct_mime_type = false;
        if (!empty($document)) {
            $file_id = $document->get('file_id');
            $mime_type = $document->get('mime_type');

            if (in_array($mime_type, $correct_mime_types)) {
                $is_correct_mime_type = true;
            }
        }

        if (!empty($video)) {
            $file_id = $video->get('file_id');
            $mime_type = $video->get('mime_type');

            if (in_array($mime_type, $correct_mime_types)) {
                $is_correct_mime_type = true;
            }
        }

        if (!empty($photo)) {
            $file_id = $photo[0]['file_id'];
            $mime_type = $photo->get('mime_type');

            if (!empty($mime_type)) {
                if (in_array($mime_type, $correct_mime_types)) {
                    $is_correct_mime_type = true;
                }
            } else {
                $is_correct_mime_type = true;
            }
        }

        $this->logger->critical($mime_type);

        try {
            $file_path = $this->api->getFile(['file_id' => $file_id])->get('file_path');
        } catch (TelegramSDKException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }

        $dir = substr($file_path, 0, stripos($file_path, '/'));

        switch ($dir) {
            case 'documents':
                $file_type = BaseAbstract::FILE_TYPE_DOCUMENT;
                break;
            case 'videos':
                $file_type = BaseAbstract::FILE_TYPE_VIDEO;
                break;
            case 'photos':
                $file_type = BaseAbstract::FILE_TYPE_PHOTO;
                break;
            case 'animations':
                $file_type = BaseAbstract::FILE_TYPE_ANIMATION;
                break;
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

    function whom(): void
    {
        $whom_all = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_all'] ?? false;
        $whom_course_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_course_id'] ?? null;
        $whom_promocode_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_promocode_id'] ?? null;

        $text = 'Кому будет отправлена рассылка:';
        $keyboard = (new Keyboard())->inline();

        if ($whom_all) {
            $keyboard
                ->row([
                    'text' => '✅ Всем',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_WHOM_ALL_UNSELECT
                    ])
                ]);
        } else {
            if (empty($whom_course_id) && empty($whom_promocode_id)) {
                $keyboard
                    ->row([
                        'text' => 'Всем',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_ALL
                        ])
                    ], [
                        'text' => 'Промокод',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODES
                        ])
                    ])
                    ->row([
                        'text' => 'Купившим',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_COURSES
                        ])
                    ]);
            } else {

                if (empty($whom_promocode_id)) {
                    $promocode_cell = [
                        'text' => 'Промокод',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODES
                        ])
                    ];
                } else {
                    $promocode = $this->promocodeRepository->findById($whom_promocode_id);

                    $promocode_cell = [
                        'text' => '✅ '.$promocode->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODE_UNSELECT
                        ])
                    ];
                }

                if (empty($whom_course_id)) {
                    $course_cell = [
                        'text' => 'Купившим',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_COURSES
                        ])
                    ];
                } else {
                    $course = $this->itemRepository->findById($whom_course_id);

                    $course_cell = [
                        'text' => '✅ '.$course->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_COURSE_UNSELECT
                        ])
                    ];
                }

                $keyboard->row($promocode_cell, $course_cell);
            }
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_MENU
                ])
            ], [
                'text' => 'Отправить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_SEND
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function whomSelectAll(): void
    {
        $this->getLastBotQuestion()->addAnswer('whom_all', true);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function whomUnselectAll(): void
    {
        $this->getLastBotQuestion()->unsetAnswer('whom_all');
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function whomSelectCourse(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()->addAnswer('whom_course_id', $id);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function whomUnselectCourse(): void
    {
        $this->getLastBotQuestion()->unsetAnswer('whom_course_id');
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function whomPromocodes(): void
    {
        $page = $this->getCallbackData()->p ?? 1;

        $limit = 5;

        $text = 'Выберите промокод:';

        $keyboard = (new Keyboard())->inline();

        $promocodes = $this->promocodeRepository->getList($page, $limit);
        $pages = ceil($promocodes->count() / $limit);

        if ($promocodes->count() > 0) {

            /**
             * @var Promocode $promocode
             */
            foreach ($promocodes as $promocode) {
                $keyboard
                    ->row([
                        'text' => $promocode->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODE,
                            'id' => $promocode->getId()
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
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODES,
                            'p' => $previous_page
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_MAILING_WHOM_PROMOCODES,
                            'p' => $next_page
                        ])
                    ]);
            }
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAILING_WHOM
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function whomCourses(): void
    {
        $this->courses(
            self::COMMAND_MAILING_WHOM_COURSE,
            self::COMMAND_MAILING_WHOM_COURSES,
            self::COMMAND_MAILING_WHOM
        );
    }

    function whomSelectPromocode(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()->addAnswer('whom_promocode_id', $id);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function whomUnselectPromocode(): void
    {
        $this->getLastBotQuestion()->unsetAnswer('whom_promocode_id');
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->whom();
    }

    function send(): void
    {
        $whom_all = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_all'] ?? null;
        $whom_course_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_course_id'] ?? null;
        $whom_promocode_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['whom_promocode_id'] ?? null;

        $text = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['text'];
        $item_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['item_id'] ?? null;
        $buttons = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['buttons'] ?? null;
        $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'] ?? null;
        $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'] ?? null;

        if (!empty($whom_course_id)) {
            $course = $this->itemRepository->findById($whom_course_id);
        } else {
            $course = null;
        }

        if (!empty($whom_promocode_id)) {
            $promocode = $this->promocodeRepository->findById($whom_promocode_id);
        } else {
            $promocode = null;
        }

        $users = $this->userRepository->getListForMailing($course, $promocode);

        if ($users->count() === 0) {
            $text = 'Людей по данным фильтрам не обнаружено!'.PHP_EOL.PHP_EOL.'Выберите другие критерии для рассылки!';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING_WHOM
                    ])
                ])
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
            $this->sendMessage($text, $keyboard);
        } else {

            $keyboard = (new Keyboard())->inline();

            if (!empty($item_id)) {
                $item = $this->itemRepository->findById($item_id);

                $keyboard
                    ->row([
                        'text' => '✅ '.$item->getName(),
                        'url' => $item->getAboutUrl()
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
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_DELETE_MESSAGE
                    ])
                ]);

            $count = 0;
            /**
             * @var User $user
             */
            foreach ($users as $user) {
                ++$count;

                if (!empty($file_id) && !empty($file_type)) {

                    switch ($file_type) {
                        case BaseAbstract::FILE_TYPE_ANIMATION:
                            try {
                                $this->api->sendAnimation([
                                    'chat_id' => $user->getChatId(),
                                    'animation' => $file_id,
                                    'caption' => $text,
                                    'reply_markup' => $keyboard
                                ]);
                            } catch (TelegramSDKException $e) {
                                $this->logger->critical($e->getMessage());
                                continue;
                            }
                            break;
                        case BaseAbstract::FILE_TYPE_PHOTO:
                            try {
                                $this->api->sendPhoto([
                                    'chat_id' => $user->getChatId(),
                                    'photo' => $file_id,
                                    'caption' => $text,
                                    'reply_markup' => $keyboard
                                ]);
                            } catch (TelegramSDKException $e) {
                                $this->logger->critical($e->getMessage());
                                continue;
                            }
                            break;
                        case BaseAbstract::FILE_TYPE_VIDEO:
                            try {
                                $this->api->sendVideo([
                                    'chat_id' => $user->getChatId(),
                                    'video' => $file_id,
                                    'caption' => $text,
                                    'reply_markup' => $keyboard
                                ]);
                            } catch (TelegramSDKException $e) {
                                $this->logger->critical($e->getMessage());
                                continue;
                            }
                            break;
                        case BaseAbstract::FILE_TYPE_DOCUMENT:
                            try {
                                $this->api->sendDocument([
                                    'chat_id' => $user->getChatId(),
                                    'document' => $file_id,
                                    'caption' => $text,
                                    'reply_markup' => $keyboard
                                ]);
                            } catch (TelegramSDKException $e) {
                                $this->logger->critical($e->getMessage());
                                continue;
                            }
                            break;
                    }
                    
                } else {
                    try {
                        $this->api->sendMessage([
                            'chat_id' => $user->getChatId(),
                            'text' => $text,
                            'reply_markup' => $keyboard
                        ]);
                    } catch (TelegramSDKException $e) {
                        $this->logger->critical($e->getMessage());
                        continue;
                    }
                }

                $user->setLastMailingDate(new DateTime());
                $this->userRepository->save($user);
            }

            $text = 'Рассылка по:'.PHP_EOL.PHP_EOL;

            if (empty($course) && empty($promocode)) {
                $text .= '✅ Всем'.PHP_EOL;
            }

            if (!empty($course)) {
                $text .= '✅ '. $course->getName().PHP_EOL;
            }

            if (!empty($promocode)) {
                $text .= '✅ '. $promocode->getName().PHP_EOL;
            }

            $text .= 'завершена!'.PHP_EOL.PHP_EOL.'Было отправлено '.$count.' сообщений!';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);

            $this->sendMessage($text, $keyboard);
        }
    }

}