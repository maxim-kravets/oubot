<?php


namespace App\Repository;


use App\Entity\Promocode;
use App\Entity\User;
use App\Entity\UserPromocode;

interface UserPromocodeRepositoryInterface
{
    public function isUserUsedPromocode(User $user, Promocode $promocode): bool;
    public function save(UserPromocode $entity): void;
}