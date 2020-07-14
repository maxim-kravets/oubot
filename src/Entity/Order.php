<?php

namespace App\Entity;

use App\Dto\Order as OrderDto;
use App\Repository\OrderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    const STATUS_DECLINED = 0;
    const STATUS_NOT_PAID = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REFUNDED = 3;
    const STATUS_EXPIRED = 5;
    const STATUS_FULL_PRICE_DISCOUNT = 4;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private Item $item;

    /**
     * @ORM\Column(type="float")
     */
    private float $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Promocode::class, inversedBy="orders")
     */
    private ?Promocode $promocode;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="integer")
     */
    private int $status = self::STATUS_NOT_PAID;

    /**
     * @ORM\Column(type="array")
     */
    private array $rawResponse = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPromocode(): ?Promocode
    {
        return $this->promocode;
    }

    public function setPromocode(?Promocode $promocode): self
    {
        $this->promocode = $promocode;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public static function create(OrderDto $dto): self
    {
        return (new Order())
            ->setUser($dto->getUser())
            ->setItem($dto->getItem())
            ->setDate(new DateTime('now'))
            ->setAmount($dto->getItem()->getPrice())
        ;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRawResponse(): ?array
    {
        return $this->rawResponse;
    }

    public function setRawResponse(array $rawResponse): self
    {
        $this->rawResponse = $rawResponse;

        return $this;
    }
}
