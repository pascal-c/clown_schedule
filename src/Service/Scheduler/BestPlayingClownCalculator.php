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
        private ResultComparator $resultComparator,
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
            ($this->resultApplier)($result);

            $availableClownAvailabilites = array_filter(
                $clownAvailabilities,
                fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate, $availability)
            );
            $clownAvailability = $this->clownAvailabilitySorter->sortForPlayDate($playDate, $availableClownAvailabilites)[0] ?? null;

            ($this->resultUnapplier)($result);

            $result = $result->add($playDate, $clownAvailability);
        }

        return $result;
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return array<Result>
     */
    public function __invoke(Month $month, array $playDates, array $clownAvailabilities, Result $firstResult): array
    {
        if (empty($playDates)) {
            return [Result::create($month)];
        }

        $playDate = array_pop($playDates);

        return $this->addPlayDate(
            $playDate,
            $clownAvailabilities,
            $this($month, $playDates, $clownAvailabilities),
            $firstResult,
        );
    }

    /**
     * @param array<ClownAvailability> $clownAvailabilities
     * @param array<Result>            $results
     *
     * @return array<Result> $results
     */
    private function addPlayDate(PlayDate $playDate, array $clownAvailabilities, array $results, Result $firstResult): array
    {
        $newResults = [];
        // echo "\naddPlayDate {$playDate->getTitle()}\n"; static $counter = 0;

        foreach ($results as $result) {
            ($this->resultApplier)($result);

            foreach ($this->clownAvailabilitySorter->sortForPlayDate($playDate, $clownAvailabilities) as $clownAvailability) {
                // $counter++;
                // echo "\n  handle {$playDate->getName()} {$clownAvailability->getClown()->getName()} {$counter}\n";

                if ($this->availabilityChecker->isAvailableFor($playDate, $clownAvailability)) {
                    $newResult = $result->add($playDate, $clownAvailability);
                    if (!$this->resultComparator->isDefinitelyWorseThan($newResult, $firstResult)) {
                        $newResults[] = $newResult;
                    }
                }
            }

            $newResult = $result->add($playDate, null);
            if (!$this->resultComparator->isDefinitelyWorseThan($newResult, $firstResult)) {
                $newResults[] = $newResult;
            }
            ($this->resultUnapplier)($result);
        }

        return $newResults;
    }
}
