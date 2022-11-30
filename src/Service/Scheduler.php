<?php

namespace App\Service;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Service\Scheduler\ClownAssigner;

class Scheduler
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ClownAssigner $clownAssigner,
    )
    {
    }

    public function calculate(Month $month): void
    {
        $playDates = $this->playDateRepository->byMonth($month);
        $clownAvailabilies = $this->clownAvailabilityRepository->byMonth($month);
        $this->removeClownAssignments($playDates);
        $clownCounter = array_map(
            fn(ClownAvailability $availability) =>
                [
                    'clown' => $availability->getClown(),
                    'remainingMaxPlays' => $availability->getMaxPlaysMonth(),
                ], 
            $clownAvailabilies
        );
        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignFirstClown($playDate, $clownCounter);
        }
        #orderPlayDatesByAvailabilities
        #calculateEntitledPlays
        #assignSecondClown
    }

    private function removeClownAssignments(array $playDates): void
    {
        foreach ($playDates as $playDate) {
            foreach($playDate->getPlayingClowns() as $clown) {
                $playDate->removePlayingClown($clown);
            }
        }
    }
}
