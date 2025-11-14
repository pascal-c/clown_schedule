<?php

namespace App\Component;

use App\Repository\ConfigRepository;
use App\Value\StatisticsForClownsType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_for_clowns_dropdown')]
final class StatisticsForClownsDropdownComponent
{
    public bool $showIt = true;
    public StatisticsForClownsType $currentType;
    public array $types = [];

    public function __construct(
        private ConfigRepository $configRepository,
    ) {
    }

    public function mount(StatisticsForClownsType $currentType): void
    {
        $this->currentType = $currentType;
        $this->types = StatisticsForClownsType::cases();
        $this->showIt = $this->configRepository->isFeatureCalculationActive();
    }
}
