<?php

namespace App\Entity;

use App\Dto\Promocode as PromocodeDto;
use App\Repository\PromocodeRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PromocodeRepository::class)
 */
class Promocode
{
    const TYPE_REF = 1;
    const TYPE_ONE_TIME = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\OneToOne(targetEntity=Item::class, inversedBy="promocode")
     */
    private ?Item $item;

    /**
     * @ORM\Column(type="integer")
     */
    private int $type;

    /**
     * @ORM\Column(type="integer")
     */
    private int $purchasesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $transitionsCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $discount;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $expire;

    /**
     * @ORM\OneToMany(targetEntity=PromocodeTransition::class, mappedBy="promocode")
     */
    private $promocodeTransitions;

    public function __construct()
    {
        $this->promocodeTransitions = new ArrayCollection();
    }

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

    function increasePurchaseCount(): self
    {
        $this->purchasesCount = $this->purchasesCount + 1;

        return  $this;
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

    function increaseTransitionsCount(): self
    {
        $this->transitionsCount = $this->transitionsCount + 1;

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

    public function getExpire(): ?DateTimeInterface
    {
        return $this->expire;
    }

    public function setExpire(DateTimeInterface $expire): self
    {
        $this->expire = $expire;

        return $this;
    }

    static function create(PromocodeDto $dto): self
    {
        return (new Promocode())
            ->setName($dto->getName())
            ->setItem($dto->getItem())
            ->setType($dto->getType())
            ->setDiscount($dto->getDiscount())
            ->setExpire($dto->getExpire());
    }

    /**
     * @return Collection|PromocodeTransition[]
     */
    public function getPromocodeTransitions(): Collection
    {
        return $this->promocodeTransitions;
    }

    public function addPromocodeTransition(PromocodeTransition $promocodeTransition): self
    {
        if (!$this->promocodeTransitions->contains($promocodeTransition)) {
            $this->promocodeTransitions[] = $promocodeTransition;
            $promocodeTransition->setPromocode($this);
        }

        return $this;
    }

    public function removePromocodeTransition(PromocodeTransition $promocodeTransition): self
    {
        if ($this->promocodeTransitions->contains($promocodeTransition)) {
            $this->promocodeTransitions->removeElement($promocodeTransition);
            // set the owning side to null (unless already changed)
            if ($promocodeTransition->getPromocode() === $this) {
                $promocodeTransition->setPromocode(null);
            }
        }

        return $this;
    }
}
