<?php


namespace App\Repository;


use App\Entity\Item;
use App\Entity\Order;
use App\Entity\User;

interface OrderRepositoryInterface
{
    public function getOrder(User $user, Item $item): ?Order;
    public function findById(int $id): ?Order;
    public function save(Order $order): void;
}