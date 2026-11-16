<?php

namespace App\Component;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_per_venue_navigation', template: 'components/sub_navigation.html.twig')]
class StatisticsPerVenueNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'per_year';

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount()
    {
        $this->navigationItems = [
            'per_year' => ['label' => $this->translator->trans('jährlich'), 'url' => $this->urlHelper->generate('statistics_per_venue_per_year')],
            'infinity' => ['label' => $this->translator->trans('ewig'), 'url' => $this->urlHelper->generate('statistics_per_venue_infinity')],
        ];
    }
}
