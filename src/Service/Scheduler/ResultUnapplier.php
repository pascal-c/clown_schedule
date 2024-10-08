<?php

namespace App\Service\Scheduler;

class ResultUnapplier
{
    public function __invoke(Result $result)
    {
        foreach ($result->getPlayDates() as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            $playDate->removePlayingClown($clownAvailability->getClown());
            $clownAvailability->decrCalculatedPlaysMonth();
        }
    }
}
