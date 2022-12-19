<?php

namespace App\Service;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;

class Scheduler
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ClownAssigner $clownAssigner,
        private AvailabilityChecker $availabilityChecker
    )
    {
    }

    public function calculate(Month $month): void
    {
        $playDates = $this->playDateRepository->byMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $this->removeClownAssignments($playDates);
        
        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignFirstClown($playDate, $clownAvailabilities);
        }

        $this->calculateEntitledPlays($clownAvailabilities, count($playDates) * 2);

        $playDates = $this->orderByAvailabilities($playDates, $clownAvailabilities);

        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignSecondClown($playDate, $clownAvailabilities);
        }
    }

    private function removeClownAssignments(array $playDates): void
    {
        foreach ($playDates as $playDate) {
            foreach($playDate->getPlayingClowns() as $clown) {
                $playDate->removePlayingClown($clown);
            }
        }
    }

    private function calculateEntitledPlays(array $clownAvailabilities, int $clownPlayNumber): void
    {
        $fullClownNumber = array_reduce(
            $clownAvailabilities,
            fn(float $number, ClownAvailability $availability) => $number + $availability->getAvailabilityRatio(),
            0.0
        );
        $playsPerFullClown = $clownPlayNumber / $fullClownNumber;

        foreach ($clownAvailabilities as $availability) {
            $availability->setEntitledPlaysMonth($playsPerFullClown * $availability->getAvailabilityRatio());
        }
    }

    private function orderByAvailabilities(array $playDates, array $clownAvailabilities): array
    {
        usort(
            $playDates, 
            fn(PlayDate $playDate1, PlayDate $playDate2) => 
                count(array_filter(
                    $clownAvailabilities, 
                    fn(ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate2, $availability)
                ))
                <=>
                count(array_filter(
                    $clownAvailabilities, 
                    fn(ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate1, $availability)
                ))
        );

        return $playDates;
    }
}
