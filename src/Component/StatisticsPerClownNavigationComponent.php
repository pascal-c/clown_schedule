<?php

namespace App\Component;

use App\Repository\ConfigRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_per_clown_navigation', template: 'components/sub_navigation.html.twig')]
class StatisticsPerClownNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'per_month';

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private ConfigRepository $configRepository,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount()
    {
        $this->navigationItems = array_filter(
            [
                'per_month' => ['label' => $this->translator->trans('monatlich'), 'url' => $this->urlHelper->generate('statistics'), 'hide' => !$this->configRepository->isFeatureCalculationActive()],
                'per_year' => ['label' => $this->translator->trans('jährlich'), 'url' => $this->urlHelper->generate('statistics_per_year')],
                'infinity' => ['label' => $this->translator->trans('ewig'), 'url' => $this->urlHelper->generate('statistics_infinity')],
            ],
            fn ($item) => empty($item['hide'])
        );
    }
}
