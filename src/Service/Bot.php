<?php


namespace App\Service;


use App\Service\Section\Base;
use Psr\Log\LoggerInterface;
use App\Entity\LastBotQuestion;
use App\Service\Section\BaseAbstract;
use App\Service\Section\BaseInterface;
use App\Service\Section\SupportInterface;
use App\Service\Section\CabinetInterface;
use App\Service\Section\MainMenuInterface;
use App\Service\Section\SettingsInterface;
use Symfony\Component\HttpFoundation\Request;

class Bot implements BotInterface
{
    private LoggerInterface $logger;
    private BaseInterface $baseSection;
    private MainMenuInterface $mainMenuSection;
    private SupportInterface $supportSection;
    private CabinetInterface $cabinetSection;
    private SettingsInterface $settingsSection;

    public function __construct(
        LoggerInterface $logger,
        BaseInterface $baseSection,
        MainMenuInterface $mainMenuSection,
        SupportInterface $supportSection,
        CabinetInterface $cabinetSection,
        SettingsInterface $settingsSection
    ) {
        $this->logger = $logger;
        $this->baseSection = $baseSection;
        $this->mainMenuSection = $mainMenuSection;
        $this->supportSection = $supportSection;
        $this->cabinetSection = $cabinetSection;
        $this->settingsSection = $settingsSection;
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
                case BaseAbstract::COMMAND_SUPPORT:
                    $this->supportSection->start();
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
            }
        }
    }


}