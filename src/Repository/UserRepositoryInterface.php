<?php


namespace App\Repository;


use App\Entity\User;

interface UserRepositoryInterface
{
    public function findByChatId(int $id): ?User;
    public function save(User $entity): void;
    public function remove(User $entity): void;
}