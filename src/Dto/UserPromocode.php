<?php


namespace App\Dto;


use App\Entity\User;
use App\Entity\Promocode;

class UserPromocode
{
    private User $user;
    private Promocode $promocode;

    public function __construct(User $user, Promocode $promocode)
    {
        $this->user = $user;
        $this->promocode = $promocode;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPromocode(): Promocode
    {
        return $this->promocode;
    }

}