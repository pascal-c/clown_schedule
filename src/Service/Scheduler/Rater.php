<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;

/**
 * rate a schedule to be able to compare results
 * 0 points is best.
 */
class Rater
{
    public const POINTS_PER_MISSING_CLOWN       = 100;
    public const POINTS_PER_MAX_PER_WEEK_EXCEEDED = 10;
    public const POINTS_PER_MAYBE_CLOWN        = 1;
    public const POINTS_PER_TARGET_PLAYS_MISSED  = 2;

    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ConfigRepository $configRepository,
    ) {
    }

    public function totalPoints(Month $month, bool $ignoreTargetPlays = false): int
    {
        return $this->pointsPerCategory($month, $ignoreTargetPlays)['total'];
    }

    public function pointsPerCategory(Month $month, bool $ignoreTargetPlays = false): array
    {
        $playDates = $this->playDateRepository->regularByMonth($month);
        $points = [
            'notAssigned' => 0,
            'maybeClown' => 0,
            'targetPlays' => 0,
            'maxPerWeek' => 0,
        ];

        foreach ($playDates as $playDate) {
            $points['notAssigned'] += $this->pointsForMissingClowns($playDate);
            $points['maybeClown'] += $this->pointsForMaybeClowns($playDate);
        }

        $allClownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $clownPlaysPerWeek = $this->clownPlaysPerWeek($playDates, $allClownAvailabilities);

        foreach ($allClownAvailabilities as $clownAvailability) {
            $points['targetPlays'] += $this->pointsForTargetPlaysMissed($clownAvailability, $ignoreTargetPlays);
            $playsPerWeek = $clownPlaysPerWeek[$clownAvailability->getClown()->getId()];
            if ($this->configRepository->isFeatureMaxPerWeekActive()) {
                $points['maxPerWeek'] += $this->pointsForMaxPerWeekExceeded($clownAvailability, $playsPerWeek);
            }
        }

        $points['total'] = array_sum($points);

        return $points;
    }

    private function pointsForMissingClowns(PlayDate $playDate): int
    {
        return abs(2 - $playDate->getPlayingClowns()->count()) * static::POINTS_PER_MISSING_CLOWN;
    }

    private function pointsForMaybeClowns(PlayDate $playDate): int
    {
        $points = 0;
        foreach ($playDate->getPlayingClowns() as $clown) {
            $availability = $clown->getAvailabilityFor($playDate->getMonth())?->getAvailabilityOn($playDate);
            if ('maybe' === $availability) {
                $points += static::POINTS_PER_MAYBE_CLOWN;
            }
        }

        return $points;
    }

    private function pointsForTargetPlaysMissed(ClownAvailability $clownAvailability, $ignoreTargetPlays): int
    {
        $diff = $clownAvailability->getCalculatedPlaysMonth() - $clownAvailability->getTargetPlays();

        if ($ignoreTargetPlays) { // only rate clowns who have too much plays, ignore it when they have not enough (yet)
            return max(0, $diff) * static::POINTS_PER_TARGET_PLAYS_MISSED;
        }

        return abs($diff) * static::POINTS_PER_TARGET_PLAYS_MISSED;
    }

    private function pointsForMaxPerWeekExceeded(ClownAvailability $clownAvailability, array $playsPerWeek): int
    {
        if (!$clownAvailability->getSoftMaxPlaysWeek()) {
            return 0;
        }

        $points = 0;

        foreach ($playsPerWeek as $_weekId => $plays) {
            $points += max(0, $plays - $clownAvailability->getSoftMaxPlaysWeek()) * static::POINTS_PER_MAX_PER_WEEK_EXCEEDED;
        }

        return $points;
    }

    /**
     * @return array {clownId: array{weekId: int}}
     */
    private function clownPlaysPerWeek(array $playDates, array $allClownAvailabilities): array
    {
        $clownPlaysPerWeek = [];
        $weekIds = array_unique(array_map(
            fn (PlayDate $playDate): string => $playDate->getWeek()->getId(),
            $playDates
        ));

        // initialize for every clown for every week with 0
        foreach ($weekIds as $weekId) {
            foreach ($allClownAvailabilities as $clownAvailability) {
                $clownPlaysPerWeek[$clownAvailability->getClown()->getId()][$weekId] = 0;
            }
        }

        // add 1 for every playDate
        foreach ($playDates as $playDate) {
            $weekId = $playDate->getWeek()->getId();
            foreach ($playDate->getPlayingClowns() as $clown) {
                if (array_key_exists($clown->getId(), $clownPlaysPerWeek)) {
                    ++$clownPlaysPerWeek[$clown->getId()][$weekId];
                }
            }
        }

        return $clownPlaysPerWeek;
    }
}
