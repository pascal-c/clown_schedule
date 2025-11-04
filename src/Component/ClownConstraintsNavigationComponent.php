<?php

namespace App\Component;

use App\Entity\Clown;
use App\Repository\ConfigRepository;
use App\Service\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('clown_constraints_navigation')]
class ClownConstraintsNavigationComponent
{
    public string $active;
    public ?Clown $currentClown;
    public bool $showNavigation;

    public function __construct(private AuthService $authService, private ConfigRepository $configRepository)
    {
    }

    public function mount(string $active): void
    {
        $this->active = $active;
        $this->currentClown = $this->authService->getCurrentClown();
        $this->showNavigation = $this->configRepository->isFeatureClownVenuePreferencesActive();
    }
}
