<?php


namespace App\Repository;


use App\Entity\Category;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface CategoryRepositoryInterface
{
    function findById(int $id): ?Category;
    function findByName(string $name): ?Category;
    function getList(int $page = 1, int $limit = 5): Paginator;
    function save(Category $entity): void;
}