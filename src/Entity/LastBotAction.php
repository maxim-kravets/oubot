<?php

namespace App\Entity;

use App\Repository\LastBotActionRepository;
use App\Dto\LastBotAction as LastBotActionDto;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LastBotActionRepository::class)
 */
class LastBotAction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $chatId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $messageId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $startMessageId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMessageId(): ?int
    {
        return $this->messageId;
    }

    public function setMessageId(int $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getStartMessageId(): ?int
    {
        return $this->startMessageId;
    }

    public function setStartMessageId(int $startMessageId): self
    {
        $this->startMessageId = $startMessageId;

        return $this;
    }

    public static function create(LastBotActionDto $dto): self
    {
        return (new LastBotAction())
            ->setChatId($dto->getChatId())
            ->setMessageId($dto->getMsgId())
            ->setStartMessageId($dto->getStartMsgId())
        ;
    }
}
