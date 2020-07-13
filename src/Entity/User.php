<?php

namespace App\Entity;

use App\Dto\User as UserDto;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
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
     * @ORM\Column(type="integer")
     */
    private int $chatId;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $administrator;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $lastMailingDate;

    /**
     * @ORM\OneToMany(targetEntity=Support::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $supports;

    /**
     * @ORM\OneToMany(targetEntity=UserItem::class, mappedBy="user")
     */
    private Collection $userItems;

    /**
     * @ORM\OneToMany(targetEntity=PromocodeTransition::class, mappedBy="user")
     */
    private $promocodeTransitions;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="user")
     */
    private $orders;

    public function __construct()
    {
        $this->supports = new ArrayCollection();
        $this->lastMailingDate = new DateTime('01.01.1970');
        $this->userItems = new ArrayCollection();
        $this->promocodeTransitions = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function setChatId(int $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function isAdministrator(): bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): self
    {
        $this->administrator = $administrator;

        return $this;
    }

    public function getLastMailingDate(): ?DateTimeInterface
    {
        return $this->lastMailingDate;
    }

    public function setLastMailingDate(DateTimeInterface $lastMailingDate): self
    {
        $this->lastMailingDate = $lastMailingDate;

        return $this;
    }

    /**
     * @return Collection|Support[]
     */
    public function getSupports(): Collection
    {
        return $this->supports;
    }

    public function addSupport(Support $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports[] = $support;
            $support->setUser($this);
        }

        return $this;
    }

    public function removeSupport(Support $support): self
    {
        if ($this->supports->contains($support)) {
            $this->supports->removeElement($support);
            // set the owning side to null (unless already changed)
            if ($support->getUser() === $this) {
                $support->setUser(null);
            }
        }

        return $this;
    }

    public static function create(UserDto $dto): ?User
    {
        return (new User())
            ->setName($dto->getName())
            ->setChatId($dto->getChatId())
            ->setAdministrator($dto->isAdmin())
        ;
    }

    /**
     * @return Collection|UserItem[]
     */
    public function getUserItems(): Collection
    {
        return $this->userItems;
    }

    public function addUserItem(UserItem $userItem): self
    {
        if (!$this->userItems->contains($userItem)) {
            $this->userItems[] = $userItem;
            $userItem->setUser($this);
        }

        return $this;
    }

    public function removeUserItem(UserItem $userItem): self
    {
        if ($this->userItems->contains($userItem)) {
            $this->userItems->removeElement($userItem);
            // set the owning side to null (unless already changed)
            if ($userItem->getUser() === $this) {
                $userItem->setUser(null);
            }
        }

        return $this;
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
            $promocodeTransition->setUser($this);
        }

        return $this;
    }

    public function removePromocodeTransition(PromocodeTransition $promocodeTransition): self
    {
        if ($this->promocodeTransitions->contains($promocodeTransition)) {
            $this->promocodeTransitions->removeElement($promocodeTransition);
            // set the owning side to null (unless already changed)
            if ($promocodeTransition->getUser() === $this) {
                $promocodeTransition->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }
}
