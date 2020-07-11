<?php


namespace App\Repository;


use App\Entity\Promocode;
use App\Entity\PromocodeTransition;
use App\Entity\User;

interface PromocodeTransitionRepositoryInterface
{
    function isUserTransitByPromocode(User $user, Promocode $promocode): bool;
    function save(PromocodeTransition $entity): void;
}