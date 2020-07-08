<?php


namespace App\Service\Section;


class Support extends Base implements SupportInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();
    }
}