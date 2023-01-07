<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Throwable;

class ErrorController extends AbstractController
{
    #[Route('/error', name: 'error', methods: ['GET'])]
    public function show(\Exception $exception): Response
    {
        return new Response($exception->getMessage() . "<br />" . $exception->getTraceAsString());
    }
}
