<?php


namespace App\Dto;


class LastBotQuestion
{
    private int $type;
    private int $chat_id;

    public function __construct(int $type, int $chat_id)
    {
        $this->type = $type;
        $this->chat_id = $chat_id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }
}