<?php


namespace App\Service\Section;


interface SettingsInterface
{
    function start(?string $additional_text_to_header = null): void;
    function addCourse(): void;
    function addCourseCategories(bool $delete_user_answer = false): void;
    function addCourseSelectCategory();
    function addCourseSkipCategory(): void;
    function addCourseSkipFile(): void;
    function handleUserAnswerOnAddCourseName(): void;
    function handleUserAnswerOnAddCourseCategory(): void;
    function handleUserAnswerOnAddCourseText(): void;
    function handleUserAnswerOnAddCourseFile(): void;
    function handleUserAnswerOnAddCoursePrice(): void;
    function handleUserAnswerOnAddCourseAboutUrl(): void;
    function addCourseSetVisibility(): void;
    function adminsList(): void;
    function addAdmin(): void;
    function handleUserAnswerOnAddAdminName(): void;
    function handleUserAnswerOnAddAdminChatId(): void;
    function removeAdmin(): void;
    function removeAdminConfirm(): void;
}