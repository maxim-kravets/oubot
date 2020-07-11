<?php


namespace App\Repository;


use App\Entity\Promocode;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface PromocodeRepositoryInterface
{
    function findById(int $id): ?Promocode;
    function findByName(string $name): ?Promocode;
    function getList(int $page = 1, int $limit = 5): Paginator;
    function save(Promocode $entity): void;
    function remove(Promocode $entity): void;
}