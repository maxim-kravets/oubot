<?php


namespace App\Dto;


class User
{
    private string $name;
    private int $chat_id;
    private bool $is_admin;

    public function __construct(string $name, int $chat_id, bool $is_admin)
    {
        $this->name = $name;
        $this->chat_id = $chat_id;
        $this->is_admin = $is_admin;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

}