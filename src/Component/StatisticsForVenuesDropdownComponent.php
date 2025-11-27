<?php

namespace App\Component;

use App\Value\StatisticsForVenuesType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('statistics_for_venues_dropdown', template: 'components/statistics_dropdown.html.twig')]
final class StatisticsForVenuesDropdownComponent
{
    public bool $showIt = true;
    public StatisticsForVenuesType $currentType;
    public array $types = [];
    public string $path = '';

    public function mount(): void
    {
        $this->types = StatisticsForVenuesType::cases();
    }
}
