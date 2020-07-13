<?php


namespace App\Service\Section;


use App\Entity\Item;
use Exception;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class Courses extends Base implements CoursesInterface
{
    function start(): void
    {
        $this->clearLastBotQuestion();
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

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function courses(?int $category_id = null): void
    {
        $page = $this->getCallbackData()->p ?? 1;

        if (empty($category_id)) {
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

        $items = $this->itemRepository->getList($page, 1, $category_id);
        $pages = $items->count();

        if ($items->getIterator()->count() > 0) {
            /**
             * @var Item $item
             */
            $item = $items->getIterator()[0];

            $text = '['.$item->getName().']('.$item->getAboutUrl().')';

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

            if ($this->userItemRepository->isUserHasItem($this->getUser(), $item)) {
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


            $keyboard->row($back_btn);
        } else {
            $text = '⚠️ Курсов нет';
            $keyboard = (new Keyboard())->inline()->row($back_btn);
        }

        $this->sendMessage($text, $keyboard, false, 'MarkdownV2');
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
            default:
                $this->logger->critical('Unknown file type when downloading course');
                die();
        }

        $this->getLastBotAction()->setMessageId($message->messageId);
        $this->lastBotActionRepository->save($this->getLastBotAction());

    }
}