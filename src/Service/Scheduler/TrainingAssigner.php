<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Service\PlayDateHistoryService;
use App\Value\PlayDateChangeReason;

class TrainingAssigner
{
    public function __construct(
        private AvailabilityChecker $availabilityChecker,
        private PlayDateHistoryService $playDateHistoryService,
    ) {
    }

    /**
     * @param array<ClownAvailability> $clownAvailabilities
     * @param array<PlayDate>          $trainings
     */
    public function assignAllAvailable(array $clownAvailabilities, array $trainings): void
    {
        foreach ($trainings as $training) {
            foreach ($clownAvailabilities as $clownAvailability) {
                /** var ClownAvailability $clownAvailability */
                if ($this->availabilityChecker->isAvailableOn($training, $clownAvailability)) {
                    $training->addPlayingClown($clownAvailability->getClown());
                }
            }

            $this->playDateHistoryService->add($training, null, PlayDateChangeReason::CALCULATION);
        }
    }

    public function assignOne(PlayDate $training, Clown $clown): void
    {
        $training->addPlayingClown($clown);

        $this->playDateHistoryService->add($training, $clown, PlayDateChangeReason::MANUAL_CHANGE);
    }

    public function unassignOne(PlayDate $training, Clown $clown): void
    {
        $training->removePlayingClown($clown);

        $this->playDateHistoryService->add($training, $clown, PlayDateChangeReason::MANUAL_CHANGE);
    }
}
