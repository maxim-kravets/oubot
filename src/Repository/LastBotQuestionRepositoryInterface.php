<?php


namespace App\Repository;


use App\Entity\LastBotQuestion;

interface LastBotQuestionRepositoryInterface
{
    public function findByChatId(int $id): ?LastBotQuestion;
    public function save(LastBotQuestion $entity): void;
}