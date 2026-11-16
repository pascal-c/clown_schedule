<?php

namespace App\Component;

use App\Repository\ConfigRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_navigation', template: 'components/sub_navigation.html.twig')]
class StatisticsNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'per_clown';

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private ConfigRepository $configRepository,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount()
    {
        $this->navigationItems = [
            'per_clown' => ['label' => $this->translator->trans('Nach Clowns'), 'url' => $this->urlHelper->generate('statistics')],
            'per_venue' => ['label' => $this->translator->trans('Nach Spielorten'), 'url' => $this->urlHelper->generate('statistics_per_venue_per_year')],
        ];
    }
}
