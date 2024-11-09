<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class PlayDateSorter
{
    public function __construct(private AvailabilityChecker $availabilityChecker)
    {
    }

    public function sortByAvailabilities(array $playDates, array $clownAvailabilities): array
    {
        usort(
            $playDates,
            fn (PlayDate $playDate1, PlayDate $playDate2) => count(array_filter(
                $clownAvailabilities,
                fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate1, $availability)
            ))
                <=>
                count(array_filter(
                    $clownAvailabilities,
                    fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate2, $availability)
                ))
        );

        return $playDates;
    }
}
