<?php


namespace App\Repository;


use App\Entity\UserItem;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserItemRepositoryInterface
{
    function getListByUserId(int $user_id, int $page = 1, int $limit = 5): Paginator;
    function save(UserItem $entity): void;
}