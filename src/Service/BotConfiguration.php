<?php


namespace App\Service;


class BotConfiguration implements BotConfigurationInterface
{
    public $token;

    public function __construct(?string $token)
    {
        if (empty($token)) {
            throw new \LogicException('TELEGRAM_BOT_TOKEN can\'t be empty. Fill it in .env.local');
        }

        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

}