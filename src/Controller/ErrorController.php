<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

class ErrorController extends AbstractController
{
    public function show(Exception $exception): Response
    {
        return new Response($exception->getMessage() . "<br />" . $exception->getTraceAsString());
    }
}
