<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;

class BestPlayingClownCalculator
{
    public function __construct(
        private AvailabilityChecker $availabilityChecker,
        private ClownAvailabilitySorter $clownAvailabilitySorter,
        private ResultApplier $resultApplier,
        private ResultUnapplier $resultUnapplier,
        private ResultRater $resultRater,
    ) {
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     */
    public function onlyFirst(Month $month, array $playDates, array $clownAvailabilities): Result
    {
        $result = Result::create($month);
        foreach ($playDates as $playDate) {
            $availableClownAvailabilites = array_filter(
                $clownAvailabilities,
                fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate, $availability)
            );
            $clownAvailability = $this->clownAvailabilitySorter->sortForPlayDate($playDate, $availableClownAvailabilites)[0] ?? null;

            $this->resultApplier->applyAssignment($playDate, $clownAvailability);
            $result = $result->add($playDate, $clownAvailability);
        }

        $rate = $this->resultRater->currentPoints($result, count($playDates));
        $result->setPoints($rate);
        $this->resultUnapplier->unapplyResult($result);

        return $result;
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return array<Result>
     */
    public function __invoke(Month $month, array $playDates, array $clownAvailabilities, int $firstResultRate, int $playDatesCount): array
    {
        if (empty($playDates)) {
            return [Result::create($month)];
        }

        $playDate = array_pop($playDates);

        return $this->addPlayDate(
            $playDate,
            $clownAvailabilities,
            $this($month, $playDates, $clownAvailabilities, $firstResultRate, $playDatesCount),
            $firstResultRate,
            $playDatesCount,
        );
    }

    /**
     * @param array<ClownAvailability> $clownAvailabilities
     * @param array<Result>            $results
     *
     * @return array<Result> $results
     */
    private function addPlayDate(PlayDate $playDate, array $clownAvailabilities, array $results, int $firstResultRate, int $playDatesCount): array
    {
        $newResults = [];

        foreach ($results as $result) {
            $this->resultApplier->applyResult($result);

            foreach ($this->clownAvailabilitySorter->sortForPlayDate($playDate, $clownAvailabilities) as $clownAvailability) {
                if ($this->availabilityChecker->isAvailableFor($playDate, $clownAvailability)) {
                    $newResult = $result->add($playDate, $clownAvailability);
                    $this->resultApplier->applyAssignment($playDate, $clownAvailability);

                    $newResultRate = $this->resultRater->currentPoints($newResult, $playDatesCount);
                    if ($newResultRate < $firstResultRate) {
                        $newResults[] = $newResult->setPoints($newResultRate);
                    }
                    $this->resultUnapplier->unapplyAssignment($playDate, $clownAvailability);
                }
            }

            $newResult = $result->add($playDate, null);
            $newResultRate = $this->resultRater->currentPoints($newResult, $playDatesCount);
            if ($newResultRate < $firstResultRate) {
                $newResults[] = $newResult->setPoints($newResultRate);
            }

            $this->resultUnapplier->unapplyResult($result);
        }

        return $newResults;
    }
}
