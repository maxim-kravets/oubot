<?php


namespace App\Service\Section;


use App\Dto\LastBotAction as LastBotActionDto;
use App\Dto\LastBotQuestion as LastBotQuestionDto;
use App\Dto\User as UserDto;
use App\Entity\LastBotAction;
use App\Entity\LastBotQuestion;
use App\Entity\User;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\LastBotActionRepositoryInterface;
use App\Repository\LastBotQuestionRepositoryInterface;
use App\Repository\PromocodeRepositoryInterface;
use App\Repository\SupportRepositoryInterface;
use App\Repository\UserItemRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Service\BotConfigurationInterface;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

class Base extends BaseAbstract implements BaseInterface
{
    protected LoggerInterface $logger;
    protected Api $api;
    protected int $command = self::COMMAND_UNDEFINED;
    protected int $chat_id = 0;
    protected int $message_id;
    private ?string $text = null;
    protected Update $webhookUpdate;
    protected UserRepositoryInterface $userRepository;
    protected ItemRepositoryInterface $itemRepository;
    protected SupportRepositoryInterface $supportRepository;
    protected CategoryRepositoryInterface $categoryRepository;
    protected UserItemRepositoryInterface $userItemRepository;
    protected PromocodeRepositoryInterface $promocodeRepository;
    protected LastBotActionRepositoryInterface $lastBotActionRepository;
    protected LastBotQuestionRepositoryInterface $lastBotQuestionRepository;
    private ?LastBotAction $lastBotAction;
    private ?LastBotQuestion $lastBotQuestion;
    private ?User $user;

    function __construct(
        LoggerInterface $logger,
        UserRepositoryInterface $userRepository,
        ItemRepositoryInterface $itemRepository,
        BotConfigurationInterface $botConfiguration,
        SupportRepositoryInterface $supportRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserItemRepositoryInterface $userItemRepository,
        PromocodeRepositoryInterface $promocodeRepository,
        LastBotActionRepositoryInterface $lastBotActionRepository,
        LastBotQuestionRepositoryInterface $lastBotQuestionRepository
    ) {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->itemRepository = $itemRepository;
        $this->supportRepository = $supportRepository;
        $this->categoryRepository = $categoryRepository;
        $this->userItemRepository = $userItemRepository;
        $this->promocodeRepository = $promocodeRepository;
        $this->lastBotActionRepository = $lastBotActionRepository;
        $this->lastBotQuestionRepository = $lastBotQuestionRepository;

        try {
            $this->api = new Api($botConfiguration->getToken());
        } catch (TelegramSDKException $e) {
            $this->getLogger()->critical($e->getMessage());
            die();
        }

        $this->setWebhookUpdate($this->api->getWebhookUpdate());

        switch ($this->getWebhookUpdate()->detectType()) {
            case 'callback_query':
                $this->setChatId($this->getWebhookUpdate()->get('callback_query')->getMessage()->get('chat')->get('id'));
                $this->setCommand(json_decode($this->getWebhookUpdate()->get('callback_query')->get('data'))->c);
                $this->setMessageId($this->getWebhookUpdate()->get('callback_query')->getMessage()->get('message_id'));
                break;
            case 'message':
                $this->setChatId($this->getWebhookUpdate()->getChat()->get('id'));
                $this->setText($this->getWebhookUpdate()->getMessage()->get('text'));

                if ($this->getText() === '/start') {
                    $this->setCommand(self::COMMAND_MAIN_MENU);
                }

                $this->setMessageId($this->getWebhookUpdate()->getMessage()->get('message_id'));
                break;
            default:
                $this->getLogger()->critical('Unsupported type of webhook update!');
                die();
        }

        $user = $this->userRepository->findByChatId($this->getChatId());

        if (empty($user)) {
            $username = $this->getWebhookUpdate()->getMessage()->get('from')->get('first_name');

            $dto = new UserDto($username, $this->getChatId(), false);
            $user = User::create($dto);
            $this->userRepository->save($user);
        }

        $this->setUser($user);

        $this->setLastBotAction($this->lastBotActionRepository->findByChatId($this->getChatId()));

        $lastBotQuestion = $this->lastBotQuestionRepository->findByChatId($this->getChatId());

        if (empty($lastBotQuestion)) {
            $dto = new LastBotQuestionDto(LastBotQuestion::TYPE_UNDEFINED, $this->getChatId());
            $lastBotQuestion = LastBotQuestion::create($dto);
            $this->lastBotQuestionRepository->save($lastBotQuestion);
        }

        $this->setLastBotQuestion($lastBotQuestion);

        if ($this->isBackToPreviousQuestionCmd()) {
            $question_type = json_decode($this->getWebhookUpdate()->get('callback_query')->get('data'))->qt;
            $this->getLastBotQuestion()->setType($question_type);
            $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
            $this->setCommand(self::COMMAND_UNDEFINED);
        }
    }

    function getCallbackData()
    {
        $callbackQuery = $this->getWebhookUpdate()->get('callback_query');

        if (!empty($callbackQuery)) {
            return json_decode($callbackQuery->get('data'));
        }

        return null;
    }

    function isBackToPreviousQuestionCmd(): bool
    {
        if ($this->getWebhookUpdate()->detectType() !== 'callback_query') {
            return false;
        }

        $callbackQuery = $this->getWebhookUpdate()->get('callback_query');

        if (empty($callbackQuery)) {
            return false;
        }

        $cmd = json_decode($callbackQuery->get('data'))->c ?? null;

        if (empty($cmd)) {
            return false;
        }

        return $cmd === self::COMMAND_BACK_TO_PREVIOUS_QUESTION;
    }

    function deleteMessage(?int $id = null): void
    {
        if (empty($id)) {
            $id = $this->getMessageId();
        }

        try {
            $this->api->deleteMessage([
                'chat_id' => $this->getChatId(),
                'message_id' => $id
            ]);
        } catch (TelegramSDKException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }

    function sendMessage(
        string $text,
        Keyboard $keyboard,
        bool $delete_user_answer = false,
        ?string $parse_mode = null
    ): void {
        $need_to_delete = true;
        if (empty($this->getLastBotAction())) {
            $dto = new LastBotActionDto($this->getChatId(), $this->getMessageId(), $this->getMessageId());
            $this->setLastBotAction(LastBotAction::create($dto));
            $this->lastBotActionRepository->save($this->getLastBotAction());
            $need_to_delete = false;
        } elseif ($this->getText() === '/start') {
            $this->deleteMessage($this->getLastBotAction()->getStartMessageId());
            $this->getLastBotAction()->setStartMessageId($this->getMessageId());
        } elseif ($delete_user_answer) {
            $this->deleteMessage($this->getMessageId());
        }

        if ($need_to_delete) {
            $this->deleteMessage($this->getLastBotAction()->getMessageId());
        }

        $params = [
            'chat_id' => $this->getChatId(),
            'text' => $text,
            'reply_markup' => $keyboard
        ];

        if (!empty($parse_mode)) {
            $params['parse_mode'] = $parse_mode;
        }

        try {
            $message = $this->api->sendMessage($params);
        } catch (TelegramSDKException $e) {
            $this->getLogger()->critical($e->getMessage());
            die();
        }

        $this->getLastBotAction()->setMessageId($message->messageId);
        $this->lastBotActionRepository->save($this->getLastBotAction());
    }

    function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    function getChatId()
    {
        return $this->chat_id;
    }

    function setChatId($chat_id): void
    {
        $this->chat_id = $chat_id;
    }

    function getWebhookUpdate(): Update
    {
        return $this->webhookUpdate;
    }

    function setWebhookUpdate($webhookUpdate): void
    {
        $this->webhookUpdate = $webhookUpdate;
    }

    public function getCommand(): int
    {
        return $this->command;
    }

    function setCommand(int $command): void
    {
        $this->command = $command;
    }

    function getMessageId()
    {
        return $this->message_id;
    }

    function setMessageId($message_id): void
    {
        $this->message_id = $message_id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getLastBotAction(): ?LastBotAction
    {
        return $this->lastBotAction;
    }

    public function setLastBotAction(?LastBotAction $lastBotAction): void
    {
        $this->lastBotAction = $lastBotAction;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getLastBotQuestion(): ?LastBotQuestion
    {
        return $this->lastBotQuestion;
    }

    public function setLastBotQuestion(?LastBotQuestion $lastBotQuestion): void
    {
        $this->lastBotQuestion = $lastBotQuestion;
    }

    public function isCommandDefined(): bool
    {
        return (bool) $this->getCommand();
    }

    public function clearLastBotQuestion(): void
    {
        $this->getLastBotQuestion()
            ->setType(LastBotQuestion::TYPE_UNDEFINED)
            ->unsetAnswers()
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
    }

    public function isQuestionDefined(): bool
    {
        return (bool) $this->getLastBotQuestion()->getType();
    }
}