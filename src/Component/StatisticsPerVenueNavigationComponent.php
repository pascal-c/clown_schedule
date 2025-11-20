<?php

namespace App\Component;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_per_venue_navigation', template: 'components/sub_navigation.html.twig')]
class StatisticsPerVenueNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'per_year';

    public function __construct(private UrlGeneratorInterface $urlHelper)
    {
    }

    public function mount()
    {
        $this->navigationItems = [
            'per_year' => ['label' => 'jährlich', 'url' => $this->urlHelper->generate('statistics_per_venue_per_year')],
            'infinity' => ['label' => 'ewig', 'url' => $this->urlHelper->generate('statistics_per_venue_infinity')],
        ];
    }
}
