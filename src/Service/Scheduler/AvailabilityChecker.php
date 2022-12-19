<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;

class AvailabilityChecker
{
    public function __construct(private PlayDateRepository $playDateRepository)
    {
    }

    public function isAvailableFor(PlayDate $playDate, ClownAvailability $clownAvailability): bool
    {
        $otherPlayDates = $this->playDateRepository->byMonth($clownAvailability->getMonth());
        $playDatesSameDay = array_filter(
            $otherPlayDates,
            fn($otherPlayDate) => $playDate->getDate() == $otherPlayDate->getDate() && 
                $otherPlayDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );
        $playDatesSameTimeSlot = array_filter(
            $playDatesSameDay,
            fn($playDateSamesDay) => $playDate->getDaytime() == $playDateSamesDay->getDaytime()
        );

        return 
            $clownAvailability->isAvailableOn($playDate->getDate(), $playDate->getDaytime()) && 
            $clownAvailability->getCalculatedPlaysMonth() < $clownAvailability->getMaxPlaysMonth() &&
            count($playDatesSameDay) < $clownAvailability->getMaxPlaysDay() &&
            count($playDatesSameTimeSlot) == 0 &&
            $this->notOnlyMen($playDate, $clownAvailability)
        ;
    }

    private function notOnlyMen(PlayDate $playDate, ClownAvailability $clownAvailability)
    {
        if ($playDate->getPlayingClowns()->count() != 1) {
            return true;
        }

        if ($playDate->getPlayingClowns()->first()->getGender() != 'male' ) {
            return true;
        }

        return $clownAvailability->getClown()->getGender() != 'male';
    }
}
