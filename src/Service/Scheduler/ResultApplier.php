<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class ResultApplier
{
    public function applyResult(Result $result): void
    {
        foreach ($result->getPlayDates() as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            $this->applyAssignment($playDate, $clownAvailability);
        }
    }

    public function applyAssignment(PlayDate $playDate, ?ClownAvailability $clownAvailability): void
    {
        if (!is_null($clownAvailability)) {
            $playDate->addPlayingClown($clownAvailability->getClown());
            $clownAvailability->incCalculatedPlaysMonth();
        }
    }
}
