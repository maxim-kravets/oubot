<?php

namespace App\Entity;


use App\Dto\UserItem as UserItemDto;
use App\Repository\UserItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserItemRepository::class)
 */
class UserItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="userItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private Item $item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
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

    static function create(UserItemDto $dto): self
    {
        return (new UserItem())
            ->setUser($dto->getUser())
            ->setItem($dto->getItem())
        ;
    }
}
