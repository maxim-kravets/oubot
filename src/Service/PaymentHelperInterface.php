<?php


namespace App\Service;


use App\Entity\Promocode;
use App\Entity\User;
use App\Entity\Item;
use App\Entity\Order;

interface PaymentHelperInterface
{
    public function createOrder(User $user, Item $item): Order;
    public function getFormData(Order $order): array;
    public function handleResponse(string $payment_response): string;
    public function getBuyUrl(User $user, Item $item): string;
    public function activatePromocode(Order $order, Promocode $promocode): float;
}