<?php


namespace App\Repository;


use App\Entity\Item;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface ItemRepositoryInterface
{
    function findByName(string $name): ?Item;
    function getList(int $page, int $limit = 5, ?int $category_id = null): Paginator;
    function save(Item $entity): void;
}