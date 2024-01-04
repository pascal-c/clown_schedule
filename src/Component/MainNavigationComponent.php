<?php

namespace App\Component;

use App\Entity\Clown;
use App\Service\AuthService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('main_navigation')]
class MainNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'clown';
    public ?Clown $currentClown;

    public function __construct(private UrlGeneratorInterface $urlHelper, private AuthService $authService)
    {
    }

    public function mount()
    {
        $this->currentClown = $this->authService->getCurrentClown();
        $this->navigationItems = [
            'dashboard' => ['label' => 'Dashboard', 'url' => $this->urlHelper->generate('dashboard')],
            'clown' => ['label' => 'Clowns', 'url' => $this->urlHelper->generate('clown_index')],
            'availability' => ['label' => 'Fehlzeiten', 'url' => $this->urlHelper->generate('clown_availability_index')],
            'venue' => ['label' => 'Spielorte', 'url' => $this->urlHelper->generate('venue_index')],
            'play_date' => ['label' => 'Spielplan', 'url' => $this->urlHelper->generate('schedule')],
            'statistics' => ['label' => 'Statistiken', 'url' => $this->urlHelper->generate('statistics')],
        ];
    }
}
