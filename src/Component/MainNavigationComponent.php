<?php

namespace App\Component;

use App\Entity\Clown;
use App\Repository\ConfigRepository;
use App\Service\AuthService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('main_navigation')]
class MainNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'clown';
    public ?Clown $currentClown;

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private AuthService $authService,
        private ConfigRepository $configRepository,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount()
    {
        $useCalcuation = $this->configRepository->find()->useCalculation();
        $this->currentClown = $this->authService->getCurrentClown();
        $statisticsUrl = $useCalcuation ? $this->urlHelper->generate('statistics') : $this->urlHelper->generate('statistics_per_year', ['type' => 'super']);
        $this->navigationItems = array_filter(
            [
                'dashboard' => ['label' => $this->translator->trans('Dashboard'), 'url' => $this->urlHelper->generate('dashboard')],
                'dashboard_admin' => ['label' => $this->translator->trans('Büro-Dashboard'), 'url' => $this->urlHelper->generate('dashboard_admin'), 'show' => $this->currentClown?->isAdmin()],
                'clown_constraints' => ['label' => $this->translator->trans('Wünsche'), 'url' => $this->urlHelper->generate('clown_constraints_index'), 'show' => $useCalcuation],
                'play_date' => ['label' => $this->translator->trans('Spielplan'), 'url' => $this->urlHelper->generate('schedule')],
                'statistics' => ['label' => $this->translator->trans('Statistiken'), 'url' => $statisticsUrl, 'show' => $useCalcuation || $this->currentClown?->isAdmin()],
                'clown' => ['label' => $this->translator->trans('Clowns'), 'url' => $this->urlHelper->generate('clown_index')],
                'venue' => ['label' => $this->translator->trans('Spielorte'), 'url' => $this->urlHelper->generate('venue_index')],
            ],
            fn (array $item): bool => !array_key_exists('show', $item) || $item['show']
        );
    }
}
