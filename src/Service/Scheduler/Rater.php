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
    public const POINTS_NOT_ASSIGNED       = 100;
    public const POINTS_CLOWN_MAX_PER_WEEK = 10;
    public const POINTS_CLOWN_MAYBE        = 1;
    public const POINTS_TARGET_PLAYS_MISS  = 2; // [difference] * POINTS_TARGET_PLAYS_MISS

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
            $points['notAssigned'] += $this->pointsNotAssigned($playDate);
            $points['maybeClown'] += $this->pointsMaybeClown($playDate);
        }

        $allClownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $clownPlaysPerWeek = $this->clownPlaysPerWeek($playDates, $allClownAvailabilities);

        foreach ($allClownAvailabilities as $clownAvailability) {
            $playsPerWeek = $clownPlaysPerWeek[$clownAvailability->getClown()->getId()];
            $points['targetPlays'] += $this->pointsTargetPlays($clownAvailability, $ignoreTargetPlays);
            if ($this->configRepository->hasFeatureMaxPerWeek()) {
                $points['maxPerWeek'] += $this->pointsMaxPerWeek($clownAvailability, $playsPerWeek);
            }
        }

        $points['total'] = array_sum($points);

        return $points;
    }

    private function pointsNotAssigned(PlayDate $playDate): int
    {
        return abs(2 - $playDate->getPlayingClowns()->count()) * static::POINTS_NOT_ASSIGNED;
    }

    private function pointsMaybeClown(PlayDate $playDate): int
    {
        $points = 0;
        foreach ($playDate->getPlayingClowns() as $clown) {
            $availability = $clown->getAvailabilityFor($playDate->getMonth())->getAvailabilityOn($playDate);
            if ('maybe' === $availability) {
                $points += static::POINTS_CLOWN_MAYBE;
            }
        }

        return $points;
    }

    private function pointsTargetPlays(ClownAvailability $clownAvailability, $ignoreTargetPlays): int
    {
        $diff = $clownAvailability->getCalculatedPlaysMonth() - $clownAvailability->getTargetPlays();

        if ($ignoreTargetPlays) { // only rate clowns who have too much plays, ignore it when they have not enough (yet)
            return max(0, $diff) * static::POINTS_TARGET_PLAYS_MISS;
        }

        return abs($diff) * static::POINTS_TARGET_PLAYS_MISS;
    }

    private function pointsMaxPerWeek(ClownAvailability $clownAvailability, array $playsPerWeek): int
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
            foreach ($playDate->getPlayingClowns() as $clown) {
                $weekId = $playDate->getWeek()->getId();
                ++$clownPlaysPerWeek[$clown->getId()][$weekId];
            }
        }

        return $clownPlaysPerWeek;
    }
}
