<?php


namespace App\Service\Section;


interface MailingInterface
{
    function start(): void;
    function menu(): void;
    function removeText(): void;
    function courses(): void;
    function course(): void;
    function removeCourse(): void;
    function buttons(): void;
    function file(): void;
    function removeFile(): void;
    function whom(): void;
    function whomSelectAll(): void;
    function whomUnselectAll(): void;
    function whomPromocodes(): void;
    function whomSelectPromocode(): void;
    function whomUnselectPromocode(): void;
    function whomCourses(): void;
    function whomSelectCourse(): void;
    function whomUnselectCourse(): void;
    function handleUserAnswerOnText(): void;
    function handleUserAnswerOnButtons(): void;
    function handleUserAnswerOnFile(): void;
    function send(): void;
}