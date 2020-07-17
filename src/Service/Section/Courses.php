<?php


namespace App\Service\Section;


use App\Entity\Item;
use App\Entity\LastBotQuestion;
use Exception;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class Courses extends Base implements CoursesInterface
{
    function start(): void
    {
        if ($this->getLastBotQuestion()->getType() !== LastBotQuestion::TYPE_WHERE_TO_RETURN) {
            $this->clearLastBotQuestion();
        }
        $category_id = $this->getCallbackData()->cid ?? null;

        $categories = $this->categoryRepository->getList();

        if ($categories->count() > 0 && empty($category_id)) {
            $this->categories();
        } else {
            $this->courses($category_id);
        }
    }

    function categories(): void
    {
        $page = $this->getCallbackData()->p ?? 1;
        $back_cmd = $this->getCallbackData()->bc ?? null;

        if (!empty($back_cmd)) {
            $this->getLastBotQuestion()
                ->addAnswer('cmd', $back_cmd)
                ->setType(LastBotQuestion::TYPE_WHERE_TO_RETURN)
            ;
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        }

        $limit = 5;
        $categories = $this->categoryRepository->getList($page, $limit);
        $total_count = $categories->count();
        $pages = ceil($total_count / $limit);

        try {
            $count_per_page = $categories->getIterator()->count();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            die();
        }

        $text = '💬 Выберите категорию';
        $keyboard = (new Keyboard())->inline();

        switch ($count_per_page) {
            case 1:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[0]->getId()
                        ])
                    ]);
                break;
            case 2:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[1]->getId()
                        ])
                    ]);
                break;
            case 3:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[2]->getId()
                        ])
                    ]);
                break;
            case 4:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[2]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[3]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[3]->getId()
                        ])
                    ]);
                break;
            case 5:
                $keyboard
                    ->row([
                        'text' => $categories->getIterator()[0]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[0]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[1]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[1]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[2]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[2]->getId()
                        ])
                    ])
                    ->row([
                        'text' => $categories->getIterator()[3]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[3]->getId()
                        ])
                    ], [
                        'text' => $categories->getIterator()[4]->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'cid' => $categories->getIterator()[4]->getId()
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
                        'c' => self::COMMAND_COURSES,
                        'p' => $previous_page
                    ])
                ], [
                    'text' => '▶️️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_COURSES,
                        'p' => $next_page
                    ])
                ]);
        }

        if ($this->getLastBotQuestion()->getType() === LastBotQuestion::TYPE_WHERE_TO_RETURN) {
            $back_btn = [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['cmd']
                ])
            ];
        } else {
            $back_btn = [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ];
        }

        $keyboard->row($back_btn);

        $this->sendMessage($text, $keyboard);
    }

    function courses(?int $category_id = null, ?string $additional_text_to_header = null, ?int $page = null): void
    {
        if (empty($page)) {
            $page = $this->getCallbackData()->p ?? 1;
        }

        if (empty($category_id)) {
            $category_id = $this->getCallbackData()->cid ?? null;
        }

        $back_cmd = $this->getCallbackData()->bc ?? null;

        if (!empty($back_cmd)) {
            $this->getLastBotQuestion()
                ->addAnswer('cmd', $back_cmd)
                ->setType(LastBotQuestion::TYPE_WHERE_TO_RETURN)
            ;
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        }

        if ($this->getLastBotQuestion()->getType() === LastBotQuestion::TYPE_WHERE_TO_RETURN && empty($category_id)) {
            $back_btn = [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['cmd']
                ])
            ];
        } elseif (empty($category_id)) {
            $back_btn = [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ];
        } else {
            $back_btn = [
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_COURSES
                ])
            ];
        }

        $only_visible = true;
        if ($this->getUser()->isAdministrator()) {
            $only_visible = false;
        }

        $items = $this->itemRepository->getList($page, 1, $category_id, $only_visible);
        $pages = $items->count();

        if ($items->getIterator()->count() > 0) {
            /**
             * @var Item $item
             */
            $item = $items->getIterator()[0];

            $text = '<a href="'.$item->getAboutUrl().'">'.$item->getName().'</a>';

            if (!empty($additional_text_to_header)) {
                $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
            }

            $keyboard = (new Keyboard())->inline();

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
                            'c' => self::COMMAND_COURSES,
                            'p' => $previous_page,
                            'cid' => $category_id
                        ])
                    ], [
                        'text' => $page.'/'.$pages,
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_UNDEFINED
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES,
                            'p' => $next_page,
                            'cid' => $category_id
                        ])
                    ]);
            }

            if ($this->userItemRepository->isUserHasItem($this->getUser(), $item) || $this->getUser()->isAdministrator()) {
                $back_cmd = [
                    'c' => self::COMMAND_COURSES,
                    'p' => $page
                ];

                if (!empty($category_id)) {
                    $back_cmd['cid'] = $category_id;
                }

                $first_cell = [
                    'text' => '✅ Скачать',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_COURSES_DOWNLOAD,
                        'id' => $item->getId(),
                        'bc' => $back_cmd
                    ])
                ];
            } else {
                $first_cell = [
                    'text' => '💸 Приобрести',
                    'url' => $this->paymentHelper->getBuyUrl($this->getUser(), $item)
                ];
            }

            $keyboard
                ->row($first_cell, [
                    'text' => '📖 Подробнее',
                    'url' => $item->getAboutUrl()
                ]);

            if ($this->getUser()->isAdministrator()) {

                $second_cell = [
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_COURSES_CHANGE_VISIBILITY,
                        'id' => $item->getId(),
                        'p' => $page
                    ])
                ];

                if ($item->getVisible()) {
                    $second_cell['text'] = '👀 Видимый';
                } else {
                    $second_cell['text'] = '📦 Невидимый';
                }

                $keyboard
                    ->row([
                        'text' => '🗑 Удалить',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES_REMOVE,
                            'id' => $item->getId(),
                            'p' => $page
                        ])
                    ], $second_cell);
            }

            $keyboard->row($back_btn);
        } else {
            $text = '⚠️ Курсов нет';

            if (!empty($additional_text_to_header)) {
                $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
            }

            $keyboard = (new Keyboard())->inline()->row($back_btn);
        }

        $this->sendMessage($text, $keyboard, false, 'HTML');
    }

    function changeVisibility(): void
    {
        $id = $this->getCallbackData()->id;
        $page = $this->getCallbackData()->p;

        $item = $this->itemRepository->findById($id);

        if ($item->getVisible()) {
            $item->setVisible(false);
        } else {
            $item->setVisible(true);
        }

        $category_id = null;
        if (!empty($item->getCategory())) {
            $category_id = $item->getCategory()->getId();
        }

        $this->itemRepository->save($item);
        $this->courses($category_id, null, $page);
    }

    function download(): void
    {
        $id = $this->getCallbackData()->id;
        $back_cmd = $this->getCallbackData()->bc;

        $item = $this->itemRepository->findById($id);

        $text = '✅ '.$item->getName().PHP_EOL.PHP_EOL.$item->getText();
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode($back_cmd)
            ]);

        try {
            $this->api->deleteMessage([
                'chat_id' => $this->getChatId(),
                'message_id' => $this->getLastBotAction()->getMessageId()
            ]);
        } catch (TelegramSDKException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }

        switch ($item->getFileType()) {
            case Item::FILE_TYPE_DOCUMENT:
                try {
                    $message = $this->api->sendDocument([
                        'chat_id' => $this->getChatId(),
                        'document' => $item->getFileId(),
                        'caption' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }
                break;
            case Item::FILE_TYPE_VIDEO:
                try {
                    $message = $this->api->sendVideo([
                        'chat_id' => $this->getChatId(),
                        'video' => $item->getFileId(),
                        'caption' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }
                break;
            case Item::FILE_TYPE_PHOTO:
                try {
                    $message = $this->api->sendPhoto([
                        'chat_id' => $this->getChatId(),
                        'photo' => $item->getFileId(),
                        'caption' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }
                break;
            case null:
                try {
                    $message = $this->api->sendMessage([
                        'chat_id' => $this->getChatId(),
                        'text' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e);
                    die();
                }
                break;
            default:
                $this->logger->critical('Unknown file type when downloading course');
                die();
        }

        $this->getLastBotAction()->setMessageId($message->messageId);
        $this->lastBotActionRepository->save($this->getLastBotAction());

    }

    public function removeCourse(): void
    {
        $id = $this->getCallbackData()->id;
        $page = $this->getCallbackData()->p ?? 1;

        $item = $this->itemRepository->findById($id);

        $category_id = null;
        if (!empty($item->getCategory())) {
            $category_id = $item->getCategory()->getId();
        }

        $text = 'Вы точно хотите удалить "'.$item->getName().'"?';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Да',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_COURSES_REMOVE_CONFIRM,
                    'id' => $id
                ])
            ], [
                'text' => 'Нет',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_COURSES,
                    'cid' => $category_id,
                    'p' => $page
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    public function removeCourseConfirm(): void
    {
        $id = $this->getCallbackData()->id;

        $item = $this->itemRepository->findById($id);

        $category_id = null;
        if (!empty($item)) {
            $category = $item->getCategory();

            if (!empty($category)) {
                $category_id = $category->getId();
            }
            $this->itemRepository->remove($item);

            $this->courses($category_id, '✅ Курс "'.$item->getName().'" успешно удален!');
        } else {
            $this->courses();
        }
    }
}