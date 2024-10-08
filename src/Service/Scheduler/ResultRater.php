<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\ConfigRepository;

/**
 * rate a result to be able to compare the value of possible results
 * 0 is best.
 */
class ResultRater
{
    public const POINTS_NOT_ASSIGNED       = 100;
    public const POINTS_CLOWN_MAX_PER_WEEK = 10;
    public const POINTS_CLOWN_MAYBE        = 1;
    public const POINTS_TARGET_PLAYS_MISS  = 2; // [difference] * POINTS_TARGET_PLAYS_MISS

    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ConfigRepository $configRepository,
    ) {
    }

    public function __invoke(Result $result, bool $ignoreTargetPlays = false): int
    {
        $points = 0;

        foreach ($result->getPlayDates() as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            $points += $this->rateNoClown($clownAvailability);
            $points += $this->rateMaybeClown($playDate, $clownAvailability);
        }

        $allClownAvailabilities = $this->clownAvailabilityRepository->byMonth($result->getMonth());
        $clownPlaysPerWeek = $this->clownPlaysPerWeek($result, $allClownAvailabilities);

        foreach ($allClownAvailabilities as $clownAvailability) {
            $playsPerWeek = $clownPlaysPerWeek[$clownAvailability->getClown()->getId()];
            if (!$ignoreTargetPlays) {
                $points += $this->rateTargetPlays($clownAvailability, $playsPerWeek);
            }
            if ($this->configRepository->hasFeatureMaxPerWeek()) {
                $points += $this->rateMaxPerWeek($clownAvailability, $playsPerWeek);
            }
        }

        return $points;
    }

    private function rateNoClown(?ClownAvailability $clownAvailability): int
    {
        return is_null($clownAvailability) ? static::POINTS_NOT_ASSIGNED : 0;
    }

    private function rateMaybeClown(PlayDate $playDate, ?ClownAvailability $clownAvailability): int
    {
        if (is_null($clownAvailability)) {
            return 0;
        }

        $availability = $clownAvailability->getAvailabilityOn($playDate);

        return 'maybe' === $availability ? static::POINTS_CLOWN_MAYBE : 0;
    }

    private function rateTargetPlays(ClownAvailability $clownAvailability, array $playsPerWeek): int
    {
        $playsPerMonth = array_sum($playsPerWeek);

        return abs($clownAvailability->getTargetPlays() - $playsPerMonth) * static::POINTS_TARGET_PLAYS_MISS;
    }

    private function rateMaxPerWeek(ClownAvailability $clownAvailability, array $playsPerWeek): int
    {
        if (!$clownAvailability->getSoftMaxPlaysWeek()) {
            return 0;
        }

        $points = 0;

        foreach ($playsPerWeek as $_weekId => $plays) {
            $points += max(0, $plays - $clownAvailability->getSoftMaxPlaysWeek()) * static::POINTS_CLOWN_MAX_PER_WEEK;
        }

        return $points;
    }

    /**
     * @return array {clownId: array{weekId: int}}
     */
    private function clownPlaysPerWeek(Result $result, array $allClownAvailabilities): array
    {
        $clownPlaysPerWeek = [];
        $playDates = $result->getPlayDates();
        $weekIds = array_unique(array_map(
            fn (PlayDate $playDate): string => $playDate->getWeek()->getId(),
            $playDates
        ));

        // initialize for every clown vor every week with 0
        foreach ($weekIds as $weekId) {
            foreach ($allClownAvailabilities as $clownAvailability) {
                $clownPlaysPerWeek[$clownAvailability->getClown()->getId()][$weekId] = 0;
            }
        }

        // add 1 for every playDate
        foreach ($playDates as $playDate) {
            $clownAvailability = $result->getAssignedClownAvailability($playDate);
            if (!is_null($clownAvailability)) {
                $clownId = $clownAvailability->getClown()->getId();
                $weekId = $playDate->getWeek()->getId();
                ++$clownPlaysPerWeek[$clownId][$weekId];
            }
        }

        return $clownPlaysPerWeek;
    }
}
