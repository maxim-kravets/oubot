<?php


namespace App\Repository;


use App\Entity\Category;

interface CategoryRepositoryInterface
{
    function findById(int $id): ?Category;
    function findByName(string $name): ?Category;
    function save(Category $entity): void;
}