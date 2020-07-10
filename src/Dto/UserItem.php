<?php


namespace App\Dto;


use App\Entity\User;
use App\Entity\Item;

class UserItem
{
    private User $user;
    private Item $item;

    function __construct(User $user, Item $item)
    {
        $this->user = $user;
        $this->item = $item;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}