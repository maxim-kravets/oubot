<?php

namespace App\Entity;

use App\Repository\PromocodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PromocodeRepository::class)
 */
class Promocode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity=Item::class, inversedBy="promocode", cascade={"persist", "remove"})
     */
    private $item;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $purchasesCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $transitionsCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $discount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPurchasesCount(): ?int
    {
        return $this->purchasesCount;
    }

    public function setPurchasesCount(int $purchasesCount): self
    {
        $this->purchasesCount = $purchasesCount;

        return $this;
    }

    public function getTransitionsCount(): ?int
    {
        return $this->transitionsCount;
    }

    public function setTransitionsCount(int $transitionsCount): self
    {
        $this->transitionsCount = $transitionsCount;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getExpire(): ?\DateTimeInterface
    {
        return $this->expire;
    }

    public function setExpire(\DateTimeInterface $expire): self
    {
        $this->expire = $expire;

        return $this;
    }
}
