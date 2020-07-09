<?php

namespace App\Entity;


use App\Dto\Support as SupportDto;
use App\Repository\SupportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SupportRepository::class)
 */
class Support
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\Column(type="array")
     */
    private array $questions = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $answer = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="supports")
     */
    private ?User $administrator = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $answered = false;

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

    public function getQuestions(): ?array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): self
    {
        $this->questions = $questions;

        return $this;
    }

    public function addQuestion(string $question): self
    {
        $this->questions[] = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getAdministrator(): ?User
    {
        return $this->administrator;
    }

    public function setAdministrator(?User $administrator): self
    {
        $this->administrator = $administrator;

        return $this;
    }

    public function getAnswered(): ?bool
    {
        return $this->answered;
    }

    public function setAnswered(bool $answered): self
    {
        $this->answered = $answered;

        return $this;
    }

    static function create(SupportDto $dto): Support
    {
        return (new Support())
            ->setUser($dto->getUser())
            ->addQuestion($dto->getQuestion())
        ;
    }
}
