<?php


namespace App\Service\Section;


class Courses extends Base implements CoursesInterface
{
    public function start(): void
    {
        $this->clearLastBotQuestion();
    }
}