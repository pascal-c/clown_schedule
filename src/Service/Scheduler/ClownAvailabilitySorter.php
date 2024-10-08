<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class ClownAvailabilitySorter
{
    public function __construct(private AvailabilityChecker $availabilityChecker)
    {
    }

    /**
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return array<ClownAvailability>
     */
    public function sortForPlayDate(PlayDate $playDate, array $clownAvailabilities): array
    {
        usort(
            $clownAvailabilities,
            function (ClownAvailability $availability1, ClownAvailability $availability2) use ($playDate) {
                // when maxPlayWeek ist reached, the clown comes last
                $a1MaxPlaysWeekReached = $this->availabilityChecker->maxPlaysWeekReached($playDate->getWeek(), $availability1);
                $a2MaxPlaysWeekReached = $this->availabilityChecker->maxPlaysWeekReached($playDate->getWeek(), $availability2);
                if ($a1MaxPlaysWeekReached !== $a2MaxPlaysWeekReached) {
                    return $a1MaxPlaysWeekReached ? 1 : -1;
                }

                // when availability is the same, take clown with more open plays first
                $a1Availability = $availability1->getAvailabilityOn($playDate);
                $a2Availability = $availability2->getAvailabilityOn($playDate);
                if ($a1Availability == $a2Availability) {
                    return
                        $availability2->getOpenTargetPlays()
                        <=>
                        $availability1->getOpenTargetPlays();
                }

                // take available clown with 'yes' before 'maybe' clown
                return 'yes' == $a1Availability ? -1 : 1;
            }
        );

        return $clownAvailabilities;
    }
}
