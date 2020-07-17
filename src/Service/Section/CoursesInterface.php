<?php


namespace App\Service\Section;


interface CoursesInterface
{
    function start(): void;
    function download(): void;
    function removeCourse(): void;
    function removeCourseConfirm(): void;
    function changeVisibility(): void;
}