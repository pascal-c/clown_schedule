<?php

namespace App\Component\Venue;

use App\Entity\RecurringDate;
use App\Entity\Venue;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('venue/recurring_date_index')]
final class RecurringDateIndexComponent
{
    public array $recurringDates = [];

    public function __construct()
    {
    }

    public function mount(Venue $venue, string $year): void
    {
        $this->recurringDates = $venue->getRecurringDates()->filter(
            fn (RecurringDate $recurringDate) => $recurringDate->getStartDate()->format('Y') <= $year && $recurringDate->getEndDate()->format('Y') >= $year
        )->toArray();
    }
}
