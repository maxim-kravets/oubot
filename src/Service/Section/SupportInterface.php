<?php


namespace App\Service\Section;


interface SupportInterface
{
    function start(?string $additional_text_to_header = null): void;
    function question(): void;
    function toggleSupportNotificationFlag(): void;
    function handleUserAnswerOnAskQuestion(): void;
    function handleAdminAnswerOnAnswerQuestion(): void;
}