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

    public function __construct()
    {
        $this->supports = new ArrayCollection();
        $this->lastMailingDate = new DateTime('01.01.1970');
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

    public function getAdministrator(): ?bool
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
}
