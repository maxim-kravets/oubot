<?php


namespace App\Dto;


use App\Entity\User;

class Support
{
    private User $user;
    private string $question;

    public function __construct(User $user, string $question)
    {
        $this->user = $user;
        $this->question = $question;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

}
