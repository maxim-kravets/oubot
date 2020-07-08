<?php


namespace App\Repository;


use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByChatId(int $id): ?User;
    public function findAdminByName(string $name): ?User;
    public function getAdminsList(): Paginator;
    public function save(User $entity): void;
    public function remove(User $entity): void;
}