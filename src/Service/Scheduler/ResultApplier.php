<?php

namespace App\Service\Scheduler;

class ResultApplier
{
    public function __invoke(Result $result)
    {
        foreach ($result->getPlayDates() as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            $playDate->addPlayingClown($clownAvailability->getClown());
            $clownAvailability->incCalculatedPlaysMonth();
        }
    }
}
