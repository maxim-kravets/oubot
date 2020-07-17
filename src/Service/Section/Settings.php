<?php


namespace App\Service\Section;


use App\Entity\Category;
use App\Dto\Category as CategoryDto;
use App\Dto\Item as ItemDto;
use App\Dto\User as UserDto;
use App\Entity\Item;
use App\Entity\LastBotQuestion;
use App\Entity\User;
use Exception;
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

    function addCourseCategories(bool $delete_user_answer = false): void
    {
        $page = $this->getCallbackData()->p ?? 1;

        $limit = 5;
        $categories = $this->categoryRepository->getList($page, $limit);
        $total_count = $categories->count();

        try {
            $count_per_page = $categories->getIterator()->count();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            die();
        }

        if ($total_count === 0) {
            $text = '💬 Создайте категорию:';
        } else {
            $text = '💬 Выберите или создайте категорию:';
        }

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Пропустить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY
                ])
            ]);

        $pages = ceil($total_count / $limit);

        switch ($count_per_page) {
            case 1:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[0]->getId()
                        ])
                    ]);
                break;
            case 2:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[1]->getId()
                        ])
                    ]);
                break;
            case 3:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[2]->getId()
                        ])
                    ]);
                break;
            case 4:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[2]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[3]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[3]->getId()
                        ])
                    ]);
                break;
            case 5:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[2]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[3]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[3]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[4]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                            'id' => $categories->getIterator()[4]->getId()
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
                        'c' => self::COMMAND_SETTINGS_ADD_COURSE_CATEGORIES,
                        'p' => $previous_page
                    ])
                ], [
                    'text' => '▶️️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_ADD_COURSE_CATEGORIES,
                        'p' => $next_page
                    ])
                ]);
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE
                ])
            ]);

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function addCourseSelectCategory(): void
    {
        $id = $this->getCallbackData()->id;
        $this->getLastBotQuestion()
            ->addAnswer('category_id', $id)
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

    function handleUserAnswerOnAddCourseName(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
            $delete_user_answer = false;
        } else {
            $name = $this->getText();
        }

        $item = $this->itemRepository->findByName($name);

        if (!empty($item)) {
            $text = '⚠️ Данное название уже используется, придумайте другое:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS
                    ])
                ]);
            $this->sendMessage($text, $keyboard, $delete_user_answer);
        } else {
            $this->getLastBotQuestion()
                ->addAnswer('name', $name)
                ->unsetAnswer('category_id')
                ->unsetAnswer('category_name')
                ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_CATEGORY);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
            $this->addCourseCategories($delete_user_answer);
        }
    }

    function addCourseSkipCategory(): void
    {
        $this->getLastBotQuestion()
            ->addAnswer('category_id', null)
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

    function handleUserAnswerOnAddCourseCategory(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category_name'];
            $delete_user_answer = false;
        } else {
            $name = $this->getText();
        }

        $category = $this->categoryRepository->findByName($name);

        if (!empty($category)) {
            $text = '⚠️ Данное название уже используется, придумайте другое:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_ADD_COURSE
                    ])
                ]);
        } else {
            $this->getLastBotQuestion()
                ->addAnswer('category_name', $name)
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
        }

        $this->sendMessage($text, $keyboard, $delete_user_answer);
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

        $callback_data = json_encode([
            'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY
        ]);

        $category_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category_id'] ?? null;

        if (!empty($category_id)) {
            $callback_data = json_encode([
                'c' => self::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY,
                'id' => $category_id
            ]);
        }

        $category_name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category_name'] ?? null;

        if (!empty($category_name)) {
            $callback_data = json_encode([
                'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_CATEGORY
            ]);
        }

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Пропустить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADD_COURSE_SKIP_FILE
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => $callback_data
            ]);

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function addCourseSkipFile(): void
    {
        $this->getLastBotQuestion()
            ->addAnswer('file_id', null)
            ->addAnswer('file_type', null)
            ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_PRICE)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $text = '💬 Введите стоимость:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                    'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnAddCourseFile(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'] ?? null;
            $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'] ?? null;
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

        if (empty($file_id) && empty($file_type) && !$this->isBackToPreviousQuestionCmd()) {
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
                ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_PRICE)
                ->addAnswer('file_id', $file_id)
                ->addAnswer('file_type', $file_type);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

            $text = '💬 Введите стоимость:';
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

    function handleUserAnswerOnAddCoursePrice(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $price = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['price'];
            $delete_user_answer = false;
        } else {
            $price = $this->getText();
        }

        if (empty($price) || (preg_match_all('/(?<=\s|^)\d+(?=\s|$)/', $price) === 0)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите число:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                        'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT
                    ])
                ]);
        } else {
            $this->getLastBotQuestion()
                ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_ABOUT_URL)
                ->addAnswer('price', $price);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

            $text = '💬 Введите ссылку на описание:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                        'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_FILE
                    ])
                ]);
        }

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function handleUserAnswerOnAddCourseAboutUrl(): void
    {
        $url = $this->getText();

        if (empty($url)) {
            $text = '⚠️ Вы прислали что-то не то, пришлите ссылку:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                        'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_FILE
                    ])
                ]);
        } else {

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $text = '⚠️ Пришлите ссылке в формате http://example1.com:';
                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Назад',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                            'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_FILE
                        ])
                    ]);
            } else {
                $this->getLastBotQuestion()
                    ->addAnswer('about_url', $url)
                    ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_VISIBLE);
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
                            'qt' => LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_PRICE
                        ])
                    ]);
            }
        }

        $this->sendMessage($text, $keyboard, true);
    }

    function addCourseSetVisibility(): void
    {
        $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
        $category_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category_id'] ?? null;
        $category_name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['category_name'] ?? null;
        $text = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['text'];
        $file_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_id'] ?? null;
        $file_type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['file_type'] ?? null;
        $price = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['price'];
        $about_url = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['about_url'];
        $visible = json_decode($this->getWebhookUpdate()->get('callback_query')->get('data'))->v;

        $category = null;

        if (!empty($category_id)) {
            $category = $this->categoryRepository->findById($category_id);
        }

        if (!empty($category_name)) {
            $dto = new CategoryDto($category_name);
            $category = Category::create($dto);
            $this->categoryRepository->save($category);
        }

        $dto = new ItemDto($name, $category, $text, $file_id, $file_type, $price, $about_url, $visible);
        $item = Item::create($dto);
        $this->itemRepository->save($item);

        if (!empty($category)) {
            $category->addItem($item);
            $this->categoryRepository->save($category);
        }

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
            ]);

        $adminsList = $this->userRepository->getAdminsList();

        /**
         * @var User $admin
         */
        foreach ($adminsList as $admin) {
            $keyboard
                ->row([
                    'text' => $admin->getName(),
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_REMOVE_ADMIN,
                        'id' => $admin->getId()
                    ])
                ]);
        }

        $keyboard
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
        $name = $this->getText();

        $admin = $this->userRepository->findAdminByName($name);

        if (!empty($admin)) {
            $text = '⚠️ Данное имя уже используется, выберите другое:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_ADMINS_LIST
                    ])
                ]);
        } else {
            $this->getLastBotQuestion()
                ->setType(LastBotQuestion::TYPE_SETTINGS_ADD_ADMIN_CHAT_ID)
                ->addAnswer('name', $name);
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
        }

        $this->sendMessage($text, $keyboard, true);
    }

    function handleUserAnswerOnAddAdminChatId(): void
    {
        $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
        $chat_id = (int) $this->getText();

        $admin = $this->userRepository->findAdminByChatId($chat_id);

        if (!empty($admin)) {
            $text = '⚠️ Админ с таким ChatID уже существует, введите другой:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS_ADD_ADMIN
                    ])
                ]);

            $this->sendMessage($text, $keyboard, true);
        } else {

            $user = $this->userRepository->findByChatId($chat_id);
            if (!empty($user)) {
                $user->setAdministrator(true);
            } else {
                $dto = new UserDto($name, $chat_id, true);
                $user = User::create($dto);
            }

            $this->userRepository->save($user);

            $this->deleteMessage($this->getMessageId());
            $this->start('✅ Администратор ' . $name . ' успешно добавлен!');
        }
    }

    function removeAdmin(): void
    {
        $id = $this->getCallbackData()->id;
        $admin = $this->userRepository->findById($id);

        $text = 'Вы действительно хотите удалить администратора с именем "'.$admin->getName().'"?';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '🗑 Удалить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_REMOVE_ADMIN_CONFIRM,
                    'id' => $admin->getId()
                ])
            ], [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SETTINGS_ADMINS_LIST
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function removeAdminConfirm(): void
    {
        $id = json_decode($this->getWebhookUpdate()->callbackQuery->get('data'))->id;

        $admin = $this->userRepository->findById($id);
        $admin->setAdministrator(false);
        $this->userRepository->save($admin);

        if ($this->getUser()->getId() === $admin->getId()) {
            $this->mainMenu();
        } else {
            $this->start('✅ Администратор с именем "' . $admin->getName() . '" успешно удален!');
        }
    }

    private function mainMenu(): void
    {
        $this->clearLastBotQuestion();

        $text = '✅ Вы успешно удалены из списка администраторов!'.PHP_EOL.PHP_EOL.'Выберите раздел:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '👤 Кабинет',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_CABINET
                ])
            ], [
                'text' => '🎓 Все курсы',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_COURSES
                ])
            ])
            ->row([
                'text' => '📲 Служба поддержки',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SUPPORT
                ])
            ]);

        if ($this->getUser()->isAdministrator()) {
            $keyboard
                ->row([
                    'text' => '✉️ Рассылка',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAILING
                    ])
                ], [
                    'text' => '🚀 Промокоды',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES
                    ])
                ])
                ->row([
                    'text' => '⚙️ Настройки',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_SETTINGS
                    ])
                ]);
        }

        $this->sendMessage($text, $keyboard);
    }

}
