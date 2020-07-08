<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\Request;

interface BotInterface
{
    public function handleRequest(Request $request): void;
}