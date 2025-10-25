<?php

namespace App\Controller;

use App\Service\SessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClownConstraintsController extends AbstractProtectedController
{
    public function __construct(private SessionService $sessionService)
    {
    }

    #[Route('/clown-constraints', name: 'clown_constraints_index', methods: ['GET'])]
    public function index(): Response
    {
        $activeKey = $this->sessionService->getClownConstraintsNavigationKey();
        $route = match ($activeKey) {
            'wishes' => 'clown_availability_index',
            'venue_preferences' => 'clown_venue_preferences_index',
            default => 'clown_availability_index',
        };

        return $this->redirectToRoute($route);
    }
}
