<?php


namespace App\Repository;


use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserItem;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserItemRepositoryInterface
{
    function getUserItem(User $user, Item $item): ?UserItem;
    function isUserHasItem(User $user, Item $item): bool;
    function getListByUserId(int $user_id, int $page = 1, int $limit = 5): Paginator;
    function save(UserItem $entity): void;
    function remove(UserItem $entity): void;
}