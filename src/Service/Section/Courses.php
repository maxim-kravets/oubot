<?php


namespace App\Service\Section;


use App\Entity\Item;
use Exception;
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

        $items = $this->itemRepository->getList($page, 1, $category_id);
        $pages = $items->count();

        if ($items->getIterator()->count() > 0) {
            /**
             * @var Item $item
             */
            $item = $items->getIterator()[$page - 1];

            $text = '['.$item->getName().']('.$item->getAboutUrl().')';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => '💸 Приобрести',
                    'url' => 'http://google.com'
                ], [
                    'text' => '📖 Подробнее',
                    'url' => $item->getAboutUrl()
                ]);

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


            $keyboard
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
        } else {
            $text = '⚠️ Курсов нет';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_MAIN_MENU
                    ])
                ]);
        }

        $this->sendMessage($text, $keyboard, false, 'MarkdownV2');
    }
}