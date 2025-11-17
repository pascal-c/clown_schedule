<?php

namespace App\Component;

use App\Entity\Clown;
use App\Repository\ConfigRepository;
use App\Service\AuthService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('main_navigation')]
class MainNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'clown';
    public ?Clown $currentClown;

    public function __construct(private UrlGeneratorInterface $urlHelper, private AuthService $authService, private ConfigRepository $configRepository)
    {
    }

    public function mount()
    {
        $useCalcuation = $this->configRepository->find()->useCalculation();
        $this->currentClown = $this->authService->getCurrentClown();
        $statisticsUrl = $useCalcuation ? $this->urlHelper->generate('statistics') : $this->urlHelper->generate('statistics_per_year', ['type' => 'super']);
        $this->navigationItems = array_filter(
            [
                'dashboard' => ['label' => 'Dashboard', 'url' => $this->urlHelper->generate('dashboard')],
                'clown_constraints' => ['label' => 'Wünsche', 'url' => $this->urlHelper->generate('clown_constraints_index'), 'show' => $useCalcuation],
                'play_date' => ['label' => 'Spielplan', 'url' => $this->urlHelper->generate('schedule')],
                'statistics' => ['label' => 'Statistiken', 'url' => $statisticsUrl, 'show' => $useCalcuation || $this->currentClown?->isAdmin()],
                'clown' => ['label' => 'Clowns', 'url' => $this->urlHelper->generate('clown_index')],
                'venue' => ['label' => 'Spielorte', 'url' => $this->urlHelper->generate('venue_index')],
            ],
            fn (array $item): bool => !array_key_exists('show', $item) || $item['show']
        );
    }
}
