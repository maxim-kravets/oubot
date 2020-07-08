<?php


namespace App\Service\Section;


class Promocodes extends Base implements PromocodesInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();
    }
}