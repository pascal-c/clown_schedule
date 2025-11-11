<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class FairPlayCalculator
{
    /**
     * @param ClownAvailability[] $clownAvailabilities
     * @param PlayDate[]          $playDates
     */
    public function calculateAvailabilityRatios(array $clownAvailabilities, array $playDates): void
    {
        if (empty($playDates)) {
            return;
        }

        foreach ($clownAvailabilities as $availability) {
            $availablePlays = 0;
            foreach ($playDates as $playDate) {
                if ($availability->isAvailableOn($playDate)) {
                    ++$availablePlays;
                }
            }

            $availability->setAvailabilityRatio($availablePlays / count($playDates));
        }
    }

    public function calculateEntitledPlays(array $clownAvailabilities, int $totalPlays): void
    {
        $fullClownNumber = array_reduce(
            $clownAvailabilities,
            fn (float $number, ClownAvailability $availability) => $number + $availability->getAvailabilityRatio(),
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
            for ($i = 0; $i < $diff; ++$i) {
                $clownAvailability = $this->getMaxDiff($clownAvailabilities);
                $clownAvailability->decrTargetPlays();
            }
        } elseif ($diff < 0) {
            for ($i = 0; $i > $diff; --$i) {
                $clownAvailability = $this->getMinDiff($clownAvailabilities);
                if (is_null($clownAvailability)) { // maximum reached for every clown
                    break;
                }
                $clownAvailability->incTargetPlays();
            }
        }
    }

    private function getMaxDiff(array $clownAvailabilities): ClownAvailability
    {
        return array_reduce(
            $clownAvailabilities,
            fn (ClownAvailability $carry, ClownAvailability $availability) => $carry->getTargetPlays() - $carry->getEntitledPlaysMonth()
                    >
                $availability->getTargetPlays() - $availability->getEntitledPlaysMonth()
                    ?
                $carry : $availability,
            $clownAvailabilities[0]
        );
    }

    private function getMinDiff(array $clownAvailabilities): ?ClownAvailability
    {
        $availableClownAvailabilities = array_values(array_filter(
            $clownAvailabilities,
            fn (ClownAvailability $availability) => $availability->getMaxPlaysMonth() > $availability->getTargetPlays()
        ));
        if (empty($availableClownAvailabilities)) {
            return null;
        }

        return array_reduce(
            $availableClownAvailabilities,
            fn (ClownAvailability $carry, ClownAvailability $availability) => $carry->getTargetPlays() - $carry->getEntitledPlaysMonth()
                    <
                $availability->getTargetPlays() - $availability->getEntitledPlaysMonth()
                    ?
                $carry : $availability,
            $availableClownAvailabilities[0]
        );
    }
}
