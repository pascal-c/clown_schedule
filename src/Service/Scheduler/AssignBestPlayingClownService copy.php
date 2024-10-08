<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;

class AssignBestPlayingClownService
{
    public function __construct(
        private AvailabilityChecker $availabilityChecker,
        private ClownAvailabilitySorter $clownAvailabilitySorter,
    ) {
    }

    public function assign2(array $playDates, array $clownAvailabilities): array
    {
        if (empty($playDates)) {
            return [Result::create()];
        }

        $results = [];
        $playDate = array_pop($playDates);

        foreach ($this->clownAvailabilitySorter->sortForPlayDate($playDate, $clownAvailabilities) as $clownAvailability) {
            $results = array_merge($results, $this->addPlayDate2(
                $playDate,
                $clownAvailability,
                $this->assign2($playDates, $clownAvailabilities)
            ));
        }

        return $results;
    }

    private function addPlayDate2(PlayDate $playDate, ?ClownAvailability $clownAvailability, array $results): array
    {
        $newResults = [];
        echo "\naddPlayDate {$playDate->getTitle()}\n";
        static $counter = 0;

        foreach ($results as $result) {
            ++$counter;
            echo "\n  handle {$playDate->getName()} {$clownAvailability?->getClown()?->getName()} {$counter}\n";

            if ($this->availabilityChecker->isAvailableFor($playDate, $clownAvailability)) {
                $newResults[] = $result->add($playDate, $clownAvailability);
            }
        }

        foreach ($results as $result) {
            $newResults[] = $result->add($playDate, null);
        }
        var_dump($newResults);

        return $newResults;
    }

    private function addPlayDate22(PlayDate $playDate, ClownAvailability $clownAvailability, array $results): array
    {
        $newResults = [];
        echo "\naddPlayDate {$playDate->getTitle()} {$clownAvailability->getClown()->getName()}\n";
        static $counter = 0;

        foreach ($results as $result) {
            ++$counter;
            echo "\n  handle {$playDate->getName()} {$clownAvailability->getClown()->getName()} {$counter}\n";

            if ($this->availabilityChecker->isAvailableFor($playDate, $clownAvailability)) {
                $newResults[] = $result->add($playDate, $clownAvailability);
            }
        }

        // var_dump($newResults);
        return $newResults;
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return array<Result> results
     */
    public function assign(array $playDates, array $clownAvailabilities): array
    {
        if (empty($playDates)) {
            return [Result::create()];
        }

        // $results = [];
        $playDate = array_pop($playDates);

        return $this->addPlayDate(
            $playDate,
            $clownAvailabilities,
            $this->assign($playDates, $clownAvailabilities)
        );
    }

    private function addPlayDate(PlayDate $playDate, array $clownAvailabilities, array $results): array
    {
        $newResults = [];
        echo "\naddPlayDate {$playDate->getTitle()}\n";
        static $counter = 0;

        foreach ($results as $result) {
            foreach ($this->clownAvailabilitySorter->sortForPlayDate($playDate, $clownAvailabilities) as $clownAvailability) {
                ++$counter;
                echo "\n  handle {$playDate->getName()} {$clownAvailability->getClown()->getName()} {$counter}\n";

                if ($this->availabilityChecker->isAvailableFor($playDate, $clownAvailability)) {
                    $newResults[] = $result->add($playDate, $clownAvailability);
                }
            }

            $newResults[] = $result->add($playDate, null);
        }

        // var_dump($newResults);
        return $newResults;
    }
}
