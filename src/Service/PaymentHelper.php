<?php


namespace App\Service;


use App\Dto\Order as OrderDto;
use App\Dto\UserItem as UserItemDto;
use App\Dto\UserPromocode as UserPromocodeDto;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\Promocode;
use App\Entity\User;
use App\Entity\UserItem;
use App\Entity\UserPromocode;
use App\Repository\LastBotActionRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\PromocodeRepositoryInterface;
use App\Repository\UserItemRepositoryInterface;
use App\Repository\UserPromocodeRepositoryInterface;
use App\Service\Section\BaseAbstract;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;

class PaymentHelper implements PaymentHelperInterface
{
    private Api $api;
    private LoggerInterface $logger;
    private string $wayforpay_account;
    private string $wayforpay_secret;
    private string $wayforpay_domain;
    private OrderRepositoryInterface $orderRepository;
    private BotConfigurationInterface $botConfiguration;
    private UserItemRepositoryInterface $userItemRepository;
    private PromocodeRepositoryInterface $promocodeRepository;
    private UserPromocodeRepositoryInterface $userPromocodeRepository;
    private LastBotActionRepositoryInterface $lastBotActionRepository;

    public function __construct(
        string $wayforpay_account,
        string $wayforpay_secret,
        string $wayforpay_domain,
        OrderRepositoryInterface $orderRepository,
        BotConfiguration $botConfiguration,
        LoggerInterface $logger,
        UserItemRepositoryInterface $userItemRepository,
        PromocodeRepositoryInterface $promocodeRepository,
        UserPromocodeRepositoryInterface $userPromocodeRepository,
        LastBotActionRepositoryInterface $lastBotActionRepository
    ) {
        $this->logger = $logger;
        $this->wayforpay_account = $wayforpay_account;
        $this->wayforpay_secret = $wayforpay_secret;
        $this->wayforpay_domain = $wayforpay_domain;
        $this->orderRepository = $orderRepository;
        $this->botConfiguration = $botConfiguration;
        $this->userItemRepository = $userItemRepository;
        $this->promocodeRepository = $promocodeRepository;
        $this->userPromocodeRepository = $userPromocodeRepository;
        $this->lastBotActionRepository = $lastBotActionRepository;

        try {
            $this->api = new Api($botConfiguration->getToken());
        } catch (TelegramSDKException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }

    public function createOrder(User $user, Item $item): Order
    {
        $dto = new OrderDto($user, $item);
        $order = Order::create($dto);
        $this->orderRepository->save($order);

        return $order;
    }

    public function getFormData(Order $order): array
    {
        return [
            'merchantAccount' => $this->wayforpay_account,
            'merchantDomainName' => $this->wayforpay_domain,
            'orderReference' => $order->getId(),
            'orderDate' => time(),
            'serviceUrl' => 'https://'.$this->wayforpay_domain.'/payment/handle-response',
            'returnUrl' => 'https://t.me/onlineUniversityBot',
            'amount' => $order->getAmount(),
            'currency' => 'UAH',
            'productName[]' => $order->getItem()->getName(),
            'productCount[]' => 1,
            'productPrice[]' => $order->getAmount(),
            'merchantSignature' => $this->createSignature($order)
        ];
    }

    public function getBuyUrl(User $user, Item $item): string
    {
        return 'https://'.$this->wayforpay_domain.'/payment/user/'.$user->getId().'/item/'.$item->getId();
    }

    public function activatePromocode(Order $order, Promocode $promocode): array
    {
        $new_price = round($order->getAmount() - (($order->getAmount() * $promocode->getDiscount()) / 100));

        if ($new_price == 0) {
            $new_price = 0;

            $order->setStatus(Order::STATUS_FULL_PRICE_DISCOUNT);

            $dto = new UserItemDto($order->getUser(), $order->getItem());
            $userItem = UserItem::create($dto);
            $this->userItemRepository->save($userItem);

            $text = '✅ Курс успешно куплен!';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => BaseAbstract::COMMAND_DELETE_MESSAGE
                    ])
                ]);

            try {
                $this->api->sendMessage([
                    'chat_id' => $order->getUser()->getChatId(),
                    'text' => $text,
                    'reply_markup' => $keyboard
                ]);
            } catch (TelegramSDKException $e) {
                $this->logger->critical($e->getMessage());
                die();
            }

            if ($promocode->getType() === Promocode::TYPE_ONE_TIME) {
                $this->promocodeRepository->remove($promocode);
            }

        } elseif ($new_price < 1) {
            $new_price = 1;
        }

        $order->setAmount($new_price);
        $order->setPromocode($promocode);
        $this->orderRepository->save($order);

        $new_signature = $this->createSignature($order);

        return [
            'new_price' => $new_price,
            'new_signature' => $new_signature
        ];
    }

    public function handleResponse(string $payment_response): string
    {
        $this->logger->critical($payment_response);

        $payment_response = json_decode($payment_response, true);

        $order = $this->orderRepository->findById($payment_response['orderReference']);

        if ($order->getStatus() !== Order::STATUS_REFUNDED) {
            $order->setRawResponse($payment_response);

            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => '👤 Кабинет',
                    'callback_data' => json_encode([
                        'c' => BaseAbstract::COMMAND_CABINET
                    ])
                ])
                ->row([
                    'text' => 'Закрыть',
                    'callback_data' => json_encode([
                        'c' => BaseAbstract::COMMAND_DELETE_MESSAGE
                    ])
                ]);

            $is_order_status_changed = false;
            $text = '⚠️ При оплате курса произошла ошибка';
            if (
                $payment_response['transactionStatus'] === 'Approved' &&
                $order->getStatus() !== Order::STATUS_APPROVED &&
                !$this->userItemRepository->isUserHasItem($order->getUser(), $order->getItem())
            ) {
                $order->setStatus(Order::STATUS_APPROVED);

                $dto = new UserItemDto($order->getUser(), $order->getItem());
                $userItem = UserItem::create($dto);
                $this->userItemRepository->save($userItem);

                $promocode = $order->getPromocode();

                if (!empty($promocode)) {

                    if ($promocode->getType() === Promocode::TYPE_REF) {
                        $dto = new UserPromocodeDto($order->getUser(), $promocode);
                        $userPromocode = UserPromocode::create($dto);
                        $this->userPromocodeRepository->save($userPromocode);

                        $promocode->increasePurchaseCount();
                        $this->promocodeRepository->save($promocode);
                    } else {
                        $this->promocodeRepository->remove($promocode);
                    }

                }

                $text = '✅ Курс успешно куплен!';
                $is_order_status_changed = true;
            } elseif ($payment_response['transactionStatus'] === 'Declined' && $order->getStatus() !== Order::STATUS_DECLINED) {
                $order->setStatus(Order::STATUS_DECLINED);
                $is_order_status_changed = true;
            } elseif ($payment_response['transactionStatus'] === 'Refunded' && $order->getStatus() !== Order::STATUS_REFUNDED) {
                $text = '⚠️ При оплате курса произошла ошибка. Доступ к курсу заблокирован. Средства будут возвращены в ближайшее время.';
                $userItem = $this->userItemRepository->getUserItem($order->getUser(), $order->getItem());
                $this->userItemRepository->remove($userItem);
                $order->setStatus(Order::STATUS_REFUNDED);

                $order->getPromocode()->decreasePurchaseCount();
                $this->promocodeRepository->save($order->getPromocode());

                $is_order_status_changed = true;
            } elseif ($payment_response['transactionStatus'] === 'Expired' && $order->getStatus() !== Order::STATUS_EXPIRED) {
                $order->setStatus(Order::STATUS_EXPIRED);
            } elseif($payment_response['transactionStatus'] === 'Pending' && $order->getStatus() !== Order::STATUS_ANTIFRAUD_VERIFICATION) {
                $text = '⚠️ Транзакция находится в обработке, в ближайшее время вы получите уведомление о статусе транзакции';
                $order->setStatus(Order::STATUS_ANTIFRAUD_VERIFICATION);
            }

            $lastBotAction = $this->lastBotActionRepository->findByChatId($order->getUser()->getChatId());

            if ($is_order_status_changed) {

                try {
                    $this->api->deleteMessage([
                        'chat_id' => $order->getUser()->getChatId(),
                        'message_id' => $lastBotAction->getMessageId()
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }

                try {
                    $message = $this->api->sendMessage([
                        'chat_id' => $order->getUser()->getChatId(),
                        'text' => $text,
                        'reply_markup' => $keyboard
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }

                $lastBotAction->setMessageId($message->messageId);
                $this->lastBotActionRepository->save($lastBotAction);
            }

            $this->orderRepository->save($order);
        }

        $string = $order->getId().';accept;'.time();
        $signature = hash_hmac('md5', $string, $this->wayforpay_secret);

        return json_encode([
            'orderReference' => $order->getId(),
            'status' => 'accept',
            'time' => time(),
            'signature' => $signature
        ]);
    }

    private function createSignature(Order $order): string
    {
        $string = $this->wayforpay_account.';'.$this->wayforpay_domain.';'.$order->getId().';'.$order->getDate()->getTimestamp().';'.$order->getAmount().';UAH;'.$order->getItem()->getName().';1;'.$order->getAmount();

        return hash_hmac('md5', $string, $this->wayforpay_secret);
    }

}