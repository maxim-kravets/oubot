<?php

namespace App\Entity;

use App\Dto\LastBotQuestion as LastBotQuestionDto;
use App\Repository\LastBotQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LastBotQuestionRepository::class)
 */
class LastBotQuestion
{
    const TYPE_UNDEFINED = 0;
    const TYPE_SETTINGS_ADD_COURSE_NAME = 1;
    const TYPE_SETTINGS_ADD_COURSE_CATEGORY = 2;
    const TYPE_SETTINGS_ADD_COURSE_TEXT = 3;
    const TYPE_SETTINGS_ADD_COURSE_FILE = 4;
    const TYPE_SETTINGS_ADD_COURSE_PRICE = 20;
    const TYPE_SETTINGS_ADD_COURSE_ABOUT_URL = 5;
    const TYPE_SETTINGS_ADD_COURSE_VISIBLE = 6;
    const TYPE_SETTINGS_ADD_ADMIN_NAME = 7;
    const TYPE_SETTINGS_ADD_ADMIN_CHAT_ID = 8;
    const TYPE_SUPPORT_USER_QUESTION = 9;
    const TYPE_SUPPORT_ADMIN_ANSWER = 10;
    const TYPE_PROMOCODES_ADD_PROMOCODE_NAME = 11;
    const TYPE_PROMOCODES_ADD_PROMOCODE_SELECT_ITEM = 12;
    const TYPE_PROMOCODES_ADD_PROMOCODE_SELECT_TYPE = 13;
    const TYPE_PROMOCODES_ADD_PROMOCODE_DISCOUNT = 14;
    const TYPE_PROMOCODES_ADD_PROMOCODE_EXPIRE = 15;
    const TYPE_PROMOCODES_EDIT_PROMOCODE_NAME = 16;
    const TYPE_PROMOCODES_EDIT_PROMOCODE_ITEM = 17;
    const TYPE_PROMOCODES_EDIT_PROMOCODE_EXPIRE = 18;
    const TYPE_PROMOCODES_EDIT_PROMOCODE_DISCOUNT = 19;
    const TYPE_MAILING_TEXT = 23;
    const TYPE_MAILING_FILE = 21;
    const TYPE_MAILING_BUTTONS = 22;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $type = self::TYPE_UNDEFINED;

    /**
     * @ORM\Column(type="array")
     */
    private array $answersFromPreviousQuestions = [];

    /**
     * @ORM\Column(type="integer")
     */
    private int $chatId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAnswersFromPreviousQuestions(): ?array
    {
        return $this->answersFromPreviousQuestions;
    }

    public function setAnswersFromPreviousQuestions(array $answersFromPreviousQuestions): self
    {
        $this->answersFromPreviousQuestions = $answersFromPreviousQuestions;

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

    public static function create(LastBotQuestionDto $dto): self
    {
        return (new LastBotQuestion())
            ->setChatId($dto->getChatId())
            ->setType($dto->getType())
        ;
    }

    public function addAnswer(string $key, $value): self
    {
        $this->answersFromPreviousQuestions[$key] = $value;

        return $this;
    }

    public function unsetAnswer(string $key): self
    {
        if (isset($this->answersFromPreviousQuestions[$key])) {
            unset($this->answersFromPreviousQuestions[$key]);
        }

        return $this;
    }

    public function unsetAnswers(): self
    {
        $this->answersFromPreviousQuestions = [];

        return $this;
    }
}
