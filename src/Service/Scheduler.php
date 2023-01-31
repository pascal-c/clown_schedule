<?php

namespace App\Service;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Repository\TimeSlotRepository;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use App\Service\Scheduler\FairPlayCalculator;

class Scheduler
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ClownAssigner $clownAssigner,
        private AvailabilityChecker $availabilityChecker,
        private FairPlayCalculator $fairPlayCalculator,
        private TimeSlotRepository $timeSlotRepository
    ) {}

    public function calculate(Month $month): void
    {
        $timeSlots = [];
        $playDates = $this->playDateRepository->regularByMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $this->removeClownAssignments($playDates, $clownAvailabilities, $month);
        
        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignFirstClown($playDate, $clownAvailabilities);
            if (!in_array([$playDate->getDate(), $playDate->getDaytime()], $timeSlots)) {
                $timeSlots[] = [$playDate->getDate(), $playDate->getDaytime()];
            }
        }

        $this->fairPlayCalculator->calculateEntitledPlays($clownAvailabilities, count($playDates) * 2);
        $this->fairPlayCalculator->calculateTargetPlays($clownAvailabilities, count($playDates) * 2);

        $playDates = $this->orderByAvailabilities($playDates, $clownAvailabilities);

        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignSecondClown($playDate, $clownAvailabilities);
        }
        foreach ($timeSlots as $timeSlot) {
            $this->clownAssigner->assignSubstitutionClown($timeSlot[0], $timeSlot[1], $clownAvailabilities);
        }
    }

    private function removeClownAssignments(array $playDates, array $clownAvailabilities, Month $month): void
    {
        foreach ($playDates as $playDate) {
            foreach($playDate->getPlayingClowns() as $clown) {
                $playDate->removePlayingClown($clown);
            }
        }
        
        foreach($clownAvailabilities as $availability) {
            $availability->setCalculatedPlaysMonth(null);
            $availability->setCalculatedSubstitutions(null);
        }

        foreach($this->timeSlotRepository->byMonth($month) as $timeSlot) {
            $timeSlot->setSubstitutionClown(null);
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
