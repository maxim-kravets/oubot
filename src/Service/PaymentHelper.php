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

    public function __construct(
        string $wayforpay_account,
        string $wayforpay_secret,
        string $wayforpay_domain,
        OrderRepositoryInterface $orderRepository,
        BotConfiguration $botConfiguration,
        LoggerInterface $logger,
        UserItemRepositoryInterface $userItemRepository,
        PromocodeRepositoryInterface $promocodeRepository,
        UserPromocodeRepositoryInterface $userPromocodeRepository
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
        $string = $this->wayforpay_account.';'.$this->wayforpay_domain.';'.$order->getId().';'.time().';'.$order->getAmount().';UAH;'.$order->getItem()->getName().';1;'.$order->getAmount();
        $signature = hash_hmac('md5', $string, $this->wayforpay_secret);

        return [
            'merchantAccount' => $this->wayforpay_account,
            'merchantDomainName' => $this->wayforpay_domain,
            'orderReference' => $order->getId(),
            'orderDate' => time(),
            'serviceUrl' => 'https://'.$this->wayforpay_domain.'/payment/handle-response',
            'amount' => $order->getAmount(),
            'currency' => 'UAH',
            'productName[]' => $order->getItem()->getName(),
            'productCount[]' => 1,
            'productPrice[]' => $order->getAmount(),
            'merchantSignature' => $signature
        ];
    }

    public function getBuyUrl(User $user, Item $item): string
    {
        return 'https://'.$this->wayforpay_domain.'/payment/user/'.$user->getId().'/item/'.$item->getId();
    }

    public function activatePromocode(Order $order, Promocode $promocode): float
    {
        $new_price = $order->getAmount() - (($order->getAmount() * $promocode->getDiscount()) / 100);

        if ($new_price === 0) {
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

        } elseif ($new_price < 1) {
            $new_price = 1;
        }

        $order->setAmount($new_price);
        $order->setPromocode($promocode);
        $this->orderRepository->save($order);

        return $new_price;
    }

    public function handleResponse(string $payment_response): string
    {
        $payment_response = json_decode($payment_response, true);

        $order = $this->orderRepository->findById($payment_response['orderReference']);

        $order->setRawResponse($payment_response);

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Закрыть',
                'callback_data' => json_encode([
                    'c' => BaseAbstract::COMMAND_DELETE_MESSAGE
                ])
            ]);

        $text = '⚠️ При оплате курса произошла ошибка';
        if ($payment_response['transactionStatus'] === 'Approved') {
            $order->setStatus(Order::STATUS_APPROVED);

            $dto = new UserItemDto($order->getUser(), $order->getItem());
            $userItem = UserItem::create($dto);
            $this->userItemRepository->save($userItem);

            $promocode = $order->getPromocode();

            if (!empty($promocode)) {
                $dto = new UserPromocodeDto($order->getUser(), $promocode);
                $userPromocode = UserPromocode::create($dto);
                $this->userPromocodeRepository->save($userPromocode);

                $promocode->increasePurchaseCount();
                $this->promocodeRepository->save($promocode);
            }

            $text = '✅ Курс успешно куплен!';
        } elseif ($payment_response['transactionStatus'] === 'Declined') {
            $order->setStatus(Order::STATUS_DECLINED);
        } elseif ($payment_response['transactionStatus'] === 'Refunded') {
            $text = '⚠️ При оплате курса произошла ошибка. Доступ к курсу заблокирован. Средства будут возвращены в ближайшее время.';
            $userItem = $this->userItemRepository->getUserItem($order->getUser(), $order->getItem());
            $this->userItemRepository->remove($userItem);
            $order->setStatus(Order::STATUS_REFUNDED);
        } elseif ($payment_response['transactionStatus'] === 'Expired') {
            $text = '⚠️ При оплате курса произошла ошибка. Доступ к курсу заблокирован. Средства будут возвращены в ближайшее время.';
            $userItem = $this->userItemRepository->getUserItem($order->getUser(), $order->getItem());
            $this->userItemRepository->remove($userItem);
            $order->setStatus(Order::STATUS_REFUNDED);
        }

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

        $this->orderRepository->save($order);

        $string = $order->getId().';accept;'.time();
        $signature = hash_hmac('md5', $string, $this->wayforpay_secret);

        return json_encode([
            'orderReference' => $order->getId(),
            'status' => 'accept',
            'time' => time(),
            'signature' => $signature
        ]);
    }

}