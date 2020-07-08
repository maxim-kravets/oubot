<?php

namespace App\Controller;

use App\Service\BotInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private BotInterface $bot;

    public function __construct(BotInterface $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->bot->handleRequest($request);

        return new JsonResponse('success', 200);
    }
}
