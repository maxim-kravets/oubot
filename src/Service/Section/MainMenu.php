<?php


namespace App\Service\Section;


use Telegram\Bot\Keyboard\Keyboard;

class MainMenu extends Base implements MainMenuInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();

        $text = 'Выберите раздел:';
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
                    'c' => self::COMMAND_ALL_COURSES_LIST
                ])
            ])
            ->row([
                'text' => '📲 Служба поддержки',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_SUPPORT
                ])
            ])
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

        $this->sendMessage($text, $keyboard);
    }
}