<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Promocode;
use App\Repository\ItemRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\PromocodeRepositoryInterface;
use App\Repository\UserItemRepositoryInterface;
use App\Repository\UserPromocodeRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Service\PaymentHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private PaymentHelperInterface $paymentHelper;
    private UserRepositoryInterface $userRepository;
    private ItemRepositoryInterface $itemRepository;
    private OrderRepositoryInterface $orderRepository;
    private UserItemRepositoryInterface $userItemRepository;
    private PromocodeRepositoryInterface $promocodeRepository;
    private UserPromocodeRepositoryInterface $userPromocodeRepository;

    public function __construct(
        PaymentHelperInterface $paymentHelper,
        UserRepositoryInterface $userRepository,
        ItemRepositoryInterface $itemRepository,
        OrderRepositoryInterface $orderRepository,
        UserItemRepositoryInterface $userItemRepository,
        PromocodeRepositoryInterface $promocodeRepository,
        UserPromocodeRepositoryInterface $userPromocodeRepository
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->userRepository = $userRepository;
        $this->itemRepository = $itemRepository;
        $this->orderRepository = $orderRepository;
        $this->userItemRepository = $userItemRepository;
        $this->promocodeRepository = $promocodeRepository;
        $this->userPromocodeRepository = $userPromocodeRepository;
    }

    /**
     * @Route("/payment/user/{user_id}/item/{item_id}", name="payment")
     * @param int $user_id
     * @param int $item_id
     * @return Response
     */
    public function index(int $user_id, int $item_id)
    {
        $user = $this->userRepository->findById($user_id);

        if (empty($user)) {
            return new Response('Пользователь не найден', 404);
        }

        $item = $this->itemRepository->findById($item_id);

        if (empty($item)) {
            return new Response('Курс не найден', 404);
        }

        $is_bought = false;
        if ($this->userItemRepository->isUserHasItem($user, $item)) {
            $is_bought = true;
        }

        $order = $this->paymentHelper->createOrder($user, $item);

        $data = $this->paymentHelper->getFormData($order);

        return $this->render('payment/index.html.twig', [
            'item' => $item,
            'data' => $data,
            'is_bought' => $is_bought
        ]);
    }

    /**
     * @Route("/payment/activate-promocode", name="payment_activate_promocode")
     * @param Request $request
     * @return JsonResponse
     */
    public function activatePromocode(Request $request)
    {
        if ($request->isMethod('POST')) {
            $order_id = (int) $request->get('order_id');
            $promocode_name = trim((string) $request->get('promocode_name'));

            $order = $this->orderRepository->findById($order_id);

            if (empty($order))
            {
                return new JsonResponse([
                    'activated' => false,
                    'reason' => 'Заказ не найден'
                ]);
            }

            $promocode = $this->promocodeRepository->findByName($promocode_name);

            if (empty($promocode)) {
                return new JsonResponse([
                    'activated' => false,
                    'reason' => 'Промокод не найден'
                ]);
            }

            if (
                $promocode->getType() === Promocode::TYPE_ONE_TIME &&
                $this->userPromocodeRepository->isUserUsedPromocode($order->getUser(), $promocode)
            ) {
                return new JsonResponse([
                    'activated' => false,
                    'reason' => 'Вы уже использовали этот промокод'
                ]);
            }

            if ($promocode->getItem() !== $order->getItem()) {
                return new JsonResponse([
                    'activated' => false,
                    'reason' => 'Этот промокод не распространяется на данный товар'
                ]);
            }

            $data = $this->paymentHelper->activatePromocode($order, $promocode);

            return new JsonResponse([
                'activated' => true,
                'reason' => 'Промокод успешно активирован',
                'new_price' => $data['new_price'],
                'new_signature' => $data['new_signature']
            ]);
        }

        return new JsonResponse([
            'activated' => false,
            'reason' => 'Некорректный метод запроса'
        ]);
    }

    /**
     * @Route("/payment/handle-response", name="payment_handle_response")
     */
    public function handleResponse()
    {
        $payment_response = file_get_contents('php://input');

        $response = $this->paymentHelper->handleResponse($payment_response);

        return new JsonResponse($response);
    }
}
