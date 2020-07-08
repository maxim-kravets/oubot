<?php


namespace App\Repository;


use App\Entity\LastBotAction;

interface LastBotActionRepositoryInterface
{
    public function findByChatId(int $chat_id): ?LastBotAction;
    public function save(LastBotAction $entity): void;
}