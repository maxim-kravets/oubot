<?php


namespace App\Service\Section;


interface PromocodesInterface
{
    function start(?string $additional_text_to_header = null): void;
    function create(): void;
    function remove(): void;
    function courses(): void;
    function skipItem(): void;
    function selectItem(): void;
    function selectType(): void;
    function info(?int $id = null, ?string $additional_text_to_header = null): void;
    function handleUserAnswerOnAddPromocodeName(): void;
    function handleUserAnswerOnAddPromocodeDiscount(): void;
    function handleUserAnswerOnAddPromocodeExpire(): void;
    function editNameQuestion(): void;
    function editItemQuestion(): void;
    function editExpireQuestion(): void;
    function editDiscountQuestion(): void;
    function handleUserAnswerOnEditName(): void;
    function handleUserAnswerOnEditExpire(): void;
    function handleUserAnswerOnEditDiscount(): void;
}