<?php


namespace App\Service\Section;


use Telegram\Bot\Keyboard\Keyboard;

class Cabinet extends Base implements CabinetInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();

        $text = '👤 Мой кабинет'.PHP_EOL.PHP_EOL.'Здесь вы можете посмотреть свои приобретенные курсы.';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '🎓 Приобрести курс',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_CABINET_BUY_COURSE_LIST
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
}