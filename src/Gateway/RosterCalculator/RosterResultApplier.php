<?php

namespace App\Gateway\RosterCalculator;

use App\Entity\Month;
use App\Repository\ClownRepository;
use App\Repository\PlayDateRepository;
use App\Service\PlayDateHistoryService;
use App\Value\PlayDateChangeReason;

class RosterResultApplier
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownRepository $clownRepository,
        private PlayDateHistoryService $playDateHistoryService,
    ) {
    }

    public function apply(RosterResult $result, Month $month): void
    {
        foreach ($result->assignments as $assignments) {
            $playDate = $this->playDateRepository->find($assignments['shiftId']);
            foreach ($assignments['personIds'] as $clownId) {
                $clown = $this->clownRepository->find($clownId);
                $playDate->addPlayingClown($clown);
            }
            $this->playDateHistoryService->add($playDate, null, PlayDateChangeReason::CALCULATION);
        }
        foreach ($result->personalResults as $personalResult) {
            $clown = $this->clownRepository->find($personalResult['personId']);
            $clownAvailability = $clown->getAvailabilityFor($month);
            $clownAvailability->setCalculatedPlaysMonth($personalResult['calculatedShifts']);
        }
    }
}
