<?php

namespace App\Controller;

use App\Repository\ItemRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Service\PaymentHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private PaymentHelperInterface $paymentHelper;
    private UserRepositoryInterface $userRepository;
    private ItemRepositoryInterface $itemRepository;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        PaymentHelperInterface $paymentHelper,
        UserRepositoryInterface $userRepository,
        ItemRepositoryInterface $itemRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->userRepository = $userRepository;
        $this->itemRepository = $itemRepository;
        $this->orderRepository = $orderRepository;
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
            return new Response('User not found', 404);
        }

        $item = $this->itemRepository->findById($item_id);

        if (empty($item)) {
            return new Response('Item not found', 404);
        }

        $order = $this->paymentHelper->createOrder($user, $item);

        $data = $this->paymentHelper->getFormData($order);

        return $this->render('payment/index.html.twig', [
            'item_name' => $item->getName(),
            'data' => $data,
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
