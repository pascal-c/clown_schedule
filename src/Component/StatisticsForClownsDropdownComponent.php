<?php

namespace App\Component;

use App\Repository\ConfigRepository;
use App\Value\StatisticsForClownsType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_for_clowns_dropdown')]
final class StatisticsForClownsDropdownComponent
{
    public const TYPE_SUPER = 'super';
    public const TYPE_WISHED_PLAYS_MONTH = 'wishedPlaysMonth';
    public const TYPE_TARGET_PLAYS = 'targetPlays';
    public const TYPE_CALCULATED_PLAYS_MONTH = 'calculatedPlaysMonth';
    public const TYPE_SCHEDULED_PLAYS_MONTH = 'scheduledPlaysMonth';

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
