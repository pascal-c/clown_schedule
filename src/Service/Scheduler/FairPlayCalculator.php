<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;

class FairPlayCalculator
{
    public function calculateEntitledPlays(array $clownAvailabilities, int $totalPlays): void
    {
        $fullClownNumber = array_reduce(
            $clownAvailabilities,
            fn(float $number, ClownAvailability $availability) => $number + $availability->getAvailabilityRatio(),
            0.0
        );
        $playsPerFullClown = $totalPlays / $fullClownNumber;

        foreach ($clownAvailabilities as $availability) {
            $availability->setEntitledPlaysMonth($playsPerFullClown * $availability->getAvailabilityRatio());
        }
    }

    public function calculateTargetPlays(array $clownAvailabilities, int $totalPlays): void
    {
        $wishedPlaysSum = 0;
        foreach ($clownAvailabilities as $availability) {
            $availability->setTargetPlays($availability->getWishedPlaysMonth());
            $wishedPlaysSum += $availability->getWishedPlaysMonth();
        }

        $diff = $wishedPlaysSum - $totalPlays;
        if ($diff > 0) {
            for ($i=0; $i<$diff; $i++) {
                $clownAvailability = $this->getMaxDiff($clownAvailabilities);
                $clownAvailability->decrTargetPlays();
            }
        } elseif ($diff < 0) {
            for ($i=0; $i>$diff; $i--) {
                $clownAvailability = $this->getMinDiff($clownAvailabilities);
                $clownAvailability->incTargetPlays();
            }
        }
    }

    private function getMaxDiff(array $clownAvailabilities): ClownAvailability
    {
        return array_reduce(
            $clownAvailabilities,
            fn(ClownAvailability $carry, ClownAvailability $availability) => 
                $carry->getTargetPlays() - $carry->getEntitledPlaysMonth() 
                    >
                $availability->getTargetPlays() - $availability->getEntitledPlaysMonth()
                    ?
                $carry : $availability,
            $clownAvailabilities[0]
        );
    }

    private function getMinDiff(array $clownAvailabilities): ClownAvailability
    {
        return array_reduce(
            $clownAvailabilities,
            fn(ClownAvailability $carry, ClownAvailability $availability) => 
                $carry->getTargetPlays() - $carry->getEntitledPlaysMonth() 
                    <
                $availability->getTargetPlays() - $availability->getEntitledPlaysMonth()
                    ?
                $carry : $availability,
            $clownAvailabilities[0]    
        );
    }
}
