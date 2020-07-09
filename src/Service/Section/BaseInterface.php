<?php


namespace App\Service\Section;


use App\Entity\LastBotAction;
use App\Entity\LastBotQuestion;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

interface BaseInterface
{
    function deleteMessage(?int $id = null): void;
    function sendMessage(string $text, Keyboard $keyboard): void;
    function getLogger(): LoggerInterface;
    function getChatId();
    function setChatId($chat_id): void;
    function getWebhookUpdate(): Update;
    function setWebhookUpdate($webhookUpdate): void;
    function getCommand(): int;
    function setCommand(int $command): void;
    function isCommandDefined(): bool;
    function getMessageId();
    function setMessageId($message_id): void;
    function getLastBotAction(): ?LastBotAction;
    function getLastBotQuestion(): ?LastBotQuestion;
    function isQuestionDefined(): bool;
}
