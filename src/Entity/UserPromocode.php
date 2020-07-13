<?php

namespace App\Entity;

use App\Dto\UserPromocode as UserPromocodeDto;
use App\Repository\UserPromocodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserPromocodeRepository::class)
 */
class UserPromocode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userPromocodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Promocode::class, inversedBy="userPromocodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Promocode $promocode;

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

    public function getPromocode(): Promocode
    {
        return $this->promocode;
    }

    public function setPromocode(Promocode $promocode): self
    {
        $this->promocode = $promocode;

        return $this;
    }

    public static function create(UserPromocodeDto $dto): self
    {
        return (new UserPromocode())
            ->setUser($dto->getUser())
            ->setPromocode($dto->getPromocode());
    }
}
