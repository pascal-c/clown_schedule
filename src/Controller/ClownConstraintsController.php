<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ConfigRepository;
use App\Service\SessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClownConstraintsController extends AbstractProtectedController
{
    public function __construct(private SessionService $sessionService, private ConfigRepository $configRepository)
    {
    }

    #[Route('/clown-constraints', name: 'clown_constraints_index', methods: ['GET'])]
    public function index(): Response
    {
        $activeKey = $this->sessionService->getClownConstraintsNavigationKey();
        $route = match (true) {
            'wishes' === $activeKey => 'wishes_index',
            'venue_preferences' === $activeKey && $this->configRepository->isFeatureClownVenuePreferencesActive() => 'clown_venue_preferences_index',
            default => 'wishes_index',
        };

        return $this->redirectToRoute($route);
    }

    #[Route('/clown-constraints/venue-preferences', name: 'clown_venue_preferences_index', methods: ['GET'])]
    public function clownVenuePreferences(): Response
    {
        $clownId = $this->sessionService->getActiveClownId() ?? ($this->getCurrentClown()->isAdmin() ? null : $this->getCurrentClown()->getId());

        return $this->redirectToRoute('clown_venue_preferences_show', ['clownId' => $clownId]);
    }

    #[Route('/clown-constraints/wishes', name: 'wishes_index', methods: ['GET'])]
    public function wishes(): Response
    {
        $clownId = $this->sessionService->getActiveClownId() ?? ($this->getCurrentClown()->isAdmin() ? null : $this->getCurrentClown()->getId());

        if ($clownId) {
            return $this->redirectToRoute('clown_availability_show', ['clownId' => $clownId]);
        }

        return $this->redirectToRoute('clown_availability_index');
    }
}
