<?php


namespace App\Service\Section;


use App\Entity\UserItem;
use Telegram\Bot\Keyboard\Keyboard;

class Cabinet extends Base implements CabinetInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();
        $page = $this->getCallbackData()->p ?? 1;

        $limit = 5;

        $text = '👤 Мой кабинет'.PHP_EOL.PHP_EOL.'Здесь вы можете посмотреть свои приобретенные курсы.';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '🎓 Приобрести курс',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_CABINET_BUY_COURSE_LIST
                ])
            ]);

        $usersItems = $this->userItemRepository->getListByUserId($this->getUser()->getId(), $page, $limit);
        $pages = ceil($usersItems->count() / $limit);

        if ($usersItems->count() > 0) {

            /**
             * @var UserItem $userItem
             */
            foreach ($usersItems as $userItem) {
                $keyboard
                    ->row([
                        'text' => '✅ '.$userItem->getItem()->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_COURSES_DOWNLOAD,
                            'id' => $userItem->getItem()->getId(),
                            'bcid' => self::COMMAND_CABINET
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
                            'c' => self::COMMAND_CABINET,
                            'p' => $previous_page
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_CABINET,
                            'p' => $next_page
                        ])
                    ]);
            }
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
}