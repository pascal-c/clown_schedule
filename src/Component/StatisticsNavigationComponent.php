<?php

namespace App\Component;

use App\Repository\ConfigRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_navigation', template: 'components/sub_navigation.html.twig')]
class StatisticsNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'per_month';

    public function __construct(private UrlGeneratorInterface $urlHelper, private ConfigRepository $configRepository)
    {
    }

    public function mount()
    {
        $this->navigationItems = array_filter(
            [
                'per_month' => ['label' => 'monatlich', 'url' => $this->urlHelper->generate('statistics'), 'hide' => !$this->configRepository->isFeatureCalculationActive()],
                'per_year' => ['label' => 'jährlich', 'url' => $this->urlHelper->generate('statistics_per_year')],
                'infinity' => ['label' => 'ewig', 'url' => $this->urlHelper->generate('statistics_infinity')],
            ],
            fn ($item) => empty($item['hide'])
        );
    }
}
