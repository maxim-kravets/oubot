<?php


namespace App\Repository;


use App\Entity\Item;

interface ItemRepositoryInterface
{
    public function findByName(string $name): ?Item;
    public function save(Item $entity): void;
}