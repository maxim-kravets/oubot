<?php

namespace App\Entity;

use App\Dto\PromocodeTransition as PromocodeTransitionDto;
use App\Repository\PromocodeTransitionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PromocodeTransitionRepository::class)
 */
class PromocodeTransition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Promocode::class, inversedBy="promocodeTransitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private Promocode $promocode;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="promocodeTransitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPromocode(): Promocode
    {
        return $this->promocode;
    }

    public function setPromocode(Promocode $promocode): self
    {
        $this->promocode = $promocode;

        return $this;
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

    static function create(PromocodeTransitionDto $dto): self
    {
        return (new PromocodeTransition())
            ->setPromocode($dto->getPromocode())
            ->setUser($dto->getUser())
        ;
    }
}
