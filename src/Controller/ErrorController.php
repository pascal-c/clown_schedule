<?php

namespace App\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Throwable $exception): Response
    {
        return new Response($exception->getMessage() . "<br />" . $exception->getTraceAsString());
    }
}
