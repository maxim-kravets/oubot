<?php


namespace App\Service\Section;


interface PromocodesInterface
{
    function start(): void;
    function create(): void;
    function remove(): void;
    function courses(): void;
    function skipItem(): void;
    function selectItem(): void;
    function selectType(): void;
    function info(?int $id = null): void;
    function handleUserAnswerOnAddPromocodeName(): void;
    function handleUserAnswerOnAddPromocodeDiscount(): void;
    function handleUserAnswerOnAddPromocodeExpire(): void;
}