<?php


namespace App\Dto;


class LastBotAction
{
    private int $chat_id;
    private int $msg_id;
    private int $start_msg_id;

    public function __construct(int $chat_id, int $msg_id, int $start_msg_id)
    {
        $this->chat_id = $chat_id;
        $this->msg_id = $msg_id;
        $this->start_msg_id = $start_msg_id;
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }

    public function getMsgId(): int
    {
        return $this->msg_id;
    }

    public function getStartMsgId(): int
    {
        return $this->start_msg_id;
    }
}