<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class ResultUnapplier
{
    public function unapplyResult(Result $result): void
    {
        foreach ($result->getPlayDates() as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            $this->unapplyAssignment($playDate, $clownAvailability);
        }
    }

    public function unapplyAssignment(PlayDate $playDate, ?ClownAvailability $clownAvailability): void
    {
        if (!is_null($clownAvailability)) {
            $playDate->removePlayingClown($clownAvailability->getClown());
            $clownAvailability->decrCalculatedPlaysMonth();
        }
    }
}
