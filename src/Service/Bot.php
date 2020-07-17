<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use App\Entity\LastBotQuestion;
use App\Service\Section\BaseAbstract;
use App\Service\Section\BaseInterface;
use App\Service\Section\CabinetInterface;
use App\Service\Section\CoursesInterface;
use App\Service\Section\SupportInterface;
use App\Service\Section\MailingInterface;
use App\Service\Section\MainMenuInterface;
use App\Service\Section\SettingsInterface;
use App\Service\Section\PromocodesInterface;
use Symfony\Component\HttpFoundation\Request;

class Bot implements BotInterface
{
    private LoggerInterface $logger;
    private BaseInterface $baseSection;
    private SupportInterface $supportSection;
    private CabinetInterface $cabinetSection;
    private CoursesInterface $coursesSection;
    private MailingInterface $mailingSection;
    private MainMenuInterface $mainMenuSection;
    private SettingsInterface $settingsSection;
    private PromocodesInterface $promocodesSection;

    public function __construct(
        LoggerInterface $logger,
        BaseInterface $baseSection,
        SupportInterface $supportSection,
        CabinetInterface $cabinetSection,
        CoursesInterface $coursesSection,
        MailingInterface $mailingSection,
        MainMenuInterface $mainMenuSection,
        SettingsInterface $settingsSection,
        PromocodesInterface $promocodesSection
    ) {
        $this->logger = $logger;
        $this->baseSection = $baseSection;
        $this->supportSection = $supportSection;
        $this->cabinetSection = $cabinetSection;
        $this->coursesSection = $coursesSection;
        $this->mailingSection = $mailingSection;
        $this->mainMenuSection = $mainMenuSection;
        $this->settingsSection = $settingsSection;
        $this->promocodesSection = $promocodesSection;
    }

    public function handleRequest(Request $request): void
    {
        if ($this->baseSection->isCommandDefined()) {
            switch ($this->baseSection->getCommand()) {
                case BaseAbstract::COMMAND_DELETE_MESSAGE:
                    $this->baseSection->deleteMessage();
                    break;
                case BaseAbstract::COMMAND_MAIN_MENU:
                    $this->mainMenuSection->start();
                    break;
                case BaseAbstract::COMMAND_CABINET:
                    $this->cabinetSection->start();
                    break;
                case BaseAbstract::COMMAND_COURSES:
                    $this->coursesSection->start();
                    break;
                case BaseAbstract::COMMAND_COURSES_DOWNLOAD:
                    $this->coursesSection->download();
                    break;
                case BaseAbstract::COMMAND_COURSES_REMOVE:
                    $this->coursesSection->removeCourse();
                    break;
                case BaseAbstract::COMMAND_COURSES_REMOVE_CONFIRM:
                    $this->coursesSection->removeCourseConfirm();
                    break;
                case BaseAbstract::COMMAND_COURSES_CHANGE_VISIBILITY:
                    $this->coursesSection->changeVisibility();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES:
                    $this->promocodesSection->start();
                    break;
                case BaseAbstract::COMMAND_SUPPORT:
                    $this->supportSection->start();
                    break;
                case BaseAbstract::COMMAND_MAILING:
                    $this->mailingSection->start();
                    break;
                case BaseAbstract::COMMAND_MAILING_MENU:
                    $this->mailingSection->menu();
                    break;
                case BaseAbstract::COMMAND_MAILING_REMOVE_TEXT:
                    $this->mailingSection->removeText();
                    break;
                case BaseAbstract::COMMAND_MAILING_COURSES:
                    $this->mailingSection->courses();
                    break;
                case BaseAbstract::COMMAND_MAILING_COURSE:
                    $this->mailingSection->course();
                    break;
                case BaseAbstract::COMMAND_MAILING_BUTTONS:
                    $this->mailingSection->buttons();
                    break;
                case BaseAbstract::COMMAND_MAILING_FILE:
                    $this->mailingSection->file();
                    break;
                case BaseAbstract::COMMAND_MAILING_REMOVE_FILE:
                    $this->mailingSection->removeFile();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM:
                    $this->mailingSection->whom();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_ALL:
                    $this->mailingSection->whomSelectAll();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_ALL_UNSELECT:
                    $this->mailingSection->whomUnselectAll();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_PROMOCODES:
                    $this->mailingSection->whomPromocodes();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_PROMOCODE:
                    $this->mailingSection->whomSelectPromocode();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_PROMOCODE_UNSELECT:
                    $this->mailingSection->whomUnselectPromocode();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_COURSES:
                    $this->mailingSection->whomCourses();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_COURSE:
                    $this->mailingSection->whomSelectCourse();
                    break;
                case BaseAbstract::COMMAND_MAILING_WHOM_COURSE_UNSELECT:
                    $this->mailingSection->whomUnselectCourse();
                    break;
                case BaseAbstract::COMMAND_MAILING_SEND:
                    $this->mailingSection->send();
                    break;
                case BaseAbstract::COMMAND_SETTINGS:
                    $this->settingsSection->start();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE:
                    $this->settingsSection->addCourse();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE_CATEGORIES:
                    $this->settingsSection->addCourseCategories();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE_SELECT_CATEGORY:
                    $this->settingsSection->addCourseSelectCategory();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE_SKIP_CATEGORY:
                    $this->settingsSection->addCourseSkipCategory();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE_SKIP_FILE:
                    $this->settingsSection->addCourseSkipFile();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_COURSE_SET_VISIBILITY:
                    $this->settingsSection->addCourseSetVisibility();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADMINS_LIST:
                    $this->settingsSection->adminsList();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_ADD_ADMIN:
                    $this->settingsSection->addAdmin();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_REMOVE_ADMIN:
                    $this->settingsSection->removeAdmin();
                    break;
                case BaseAbstract::COMMAND_SETTINGS_REMOVE_ADMIN_CONFIRM:
                    $this->settingsSection->removeAdminConfirm();
                    break;
                case BaseAbstract::COMMAND_SUPPORT_ADMIN_QUESTION:
                    $this->supportSection->question();
                    break;
                case BaseAbstract::COMMAND_SUPPORT_ADMIN_TOGGLE_SUPPORT_NOTIFICATION_FLAG:
                    $this->supportSection->toggleSupportNotificationFlag();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_CREATE:
                    $this->promocodesSection->create();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_CREATE_ITEMS:
                    $this->promocodesSection->courses();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_CREATE_SKIP_ITEM:
                    $this->promocodesSection->skipItem();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_CREATE_SELECT_ITEM:
                    $this->promocodesSection->selectItem();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_CREATE_SELECT_TYPE:
                    $this->promocodesSection->selectType();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_INFO:
                    $this->promocodesSection->info();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_REMOVE:
                    $this->promocodesSection->remove();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_EDIT_NAME_QUESTION:
                    $this->promocodesSection->editNameQuestion();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_EDIT_ITEM_QUESTION:
                    $this->promocodesSection->editItemQuestion();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_EDIT_EXPIRE_QUESTION:
                    $this->promocodesSection->editExpireQuestion();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_EDIT_DISCOUNT_QUESTION:
                    $this->promocodesSection->editDiscountQuestion();
                    break;
                case BaseAbstract::COMMAND_PROMOCODES_EDIT_ITEM:
                    $this->promocodesSection->editItem();
                    break;
            }
        } elseif ($this->baseSection->isQuestionDefined()) {
            switch ($this->baseSection->getLastBotQuestion()->getType()) {
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_NAME:
                    $this->settingsSection->handleUserAnswerOnAddCourseName();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_CATEGORY:
                    $this->settingsSection->handleUserAnswerOnAddCourseCategory();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_TEXT:
                    $this->settingsSection->handleUserAnswerOnAddCourseText();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_FILE:
                    $this->settingsSection->handleUserAnswerOnAddCourseFile();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_PRICE:
                    $this->settingsSection->handleUserAnswerOnAddCoursePrice();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_COURSE_ABOUT_URL:
                    $this->settingsSection->handleUserAnswerOnAddCourseAboutUrl();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_ADMIN_NAME:
                    $this->settingsSection->handleUserAnswerOnAddAdminName();
                    break;
                case LastBotQuestion::TYPE_SETTINGS_ADD_ADMIN_CHAT_ID:
                    $this->settingsSection->handleUserAnswerOnAddAdminChatId();
                    break;
                case LastBotQuestion::TYPE_SUPPORT_USER_QUESTION:
                    $this->supportSection->handleUserAnswerOnAskQuestion();
                    break;
                case LastBotQuestion::TYPE_SUPPORT_ADMIN_ANSWER:
                    $this->supportSection->handleAdminAnswerOnAnswerQuestion();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_NAME:
                    $this->promocodesSection->handleUserAnswerOnAddPromocodeName();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_DISCOUNT:
                    $this->promocodesSection->handleUserAnswerOnAddPromocodeDiscount();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_EXPIRE:
                    $this->promocodesSection->handleUserAnswerOnAddPromocodeExpire();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_NAME:
                    $this->promocodesSection->handleUserAnswerOnEditName();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_EXPIRE:
                    $this->promocodesSection->handleUserAnswerOnEditExpire();
                    break;
                case LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_DISCOUNT:
                    $this->promocodesSection->handleUserAnswerOnEditDiscount();
                    break;
                case LastBotQuestion::TYPE_MAILING_TEXT:
                    $this->mailingSection->handleUserAnswerOnText();
                    break;
                case LastBotQuestion::TYPE_MAILING_BUTTONS:
                    $this->mailingSection->handleUserAnswerOnButtons();
                    break;
                case LastBotQuestion::TYPE_MAILING_FILE:
                    $this->mailingSection->handleUserAnswerOnFile();
                    break;
            }
        } else {
            $this->baseSection->deleteMessage();
        }
    }
}
