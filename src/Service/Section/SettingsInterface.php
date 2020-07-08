<?php


namespace App\Service\Section;


interface SettingsInterface
{
    function start(): void;
    function addCourse(): void;
    function addCourseSkipCategory(): void;
    function handleUserAnswerOnAddCourseName(): void;
    function handleUserAnswerOnAddCourseText(): void;
    function handleUserAnswerOnAddCourseFile(): void;
    function handleUserAnswerOnAddCourseAboutUrl(): void;
    function addCourseSetVisibility(): void;
}