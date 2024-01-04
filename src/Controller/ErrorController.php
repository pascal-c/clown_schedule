<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception): Response
    {
        return new Response($exception->getMessage().'<br />'.$exception->getTraceAsString());
    }
}
