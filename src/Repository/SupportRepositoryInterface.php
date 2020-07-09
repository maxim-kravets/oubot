<?php


namespace App\Repository;


use App\Entity\Support;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface SupportRepositoryInterface
{
    public function findById(int $id): ?Support;
    public function findUnansweredByUserId(int $id): ?Support;
    public function getListUnanswered(): Paginator;
    public function save(Support $entity): void;
}