<?php


namespace App\Dto;


use App\Entity\Item;
use DateTimeInterface;

class Promocode
{
    private string $name;
    private ?Item $item;
    private int $type;
    private int $discount;
    private DateTimeInterface $expire;

    public function __construct(string $name, ?Item $item, int $type, int $discount, DateTimeInterface $expire)
    {
        $this->name = $name;
        $this->item = $item;
        $this->type = $type;
        $this->discount = $discount;
        $this->expire = $expire;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getExpire(): DateTimeInterface
    {
        return $this->expire;
    }
}