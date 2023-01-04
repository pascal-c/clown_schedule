<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'root', methods: ['GET'])]
    public function root(): Response 
    {
        return $this->redirectToRoute('schedule');
    }
}
