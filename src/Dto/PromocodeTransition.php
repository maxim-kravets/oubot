<?php


namespace App\Dto;


use App\Entity\Promocode;
use App\Entity\User;

class PromocodeTransition
{
    private Promocode $promocode;
    private User $user;

    public function __construct(Promocode $promocode, User $user)
    {
        $this->promocode = $promocode;
        $this->user = $user;
    }

    public function getPromocode(): Promocode
    {
        return $this->promocode;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
