<?php

namespace App\Service\Scheduler\AvailabilityChecker;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Week;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use DateTimeInterface;

class MaxPlaysReachedChecker
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private ConfigRepository $configRepository,
    ) {
    }

    public function maxPlaysMonthReached(ClownAvailability $clownAvailability)
    {
        return $clownAvailability->getCalculatedPlaysMonth() >= $clownAvailability->getMaxPlaysMonth();
    }

    public function maxSubstitutionsMonthReached(ClownAvailability $clownAvailability)
    {
        return $clownAvailability->getCalculatedSubstitutions() >= $clownAvailability->getCalculatedPlaysMonth();
    }

    public function maxPlaysWeekReached(Week $week, ClownAvailability $clownAvailability): bool
    {
        $softMaxPlaysWeek = $clownAvailability->getSoftMaxPlaysWeek();
        if (is_null($softMaxPlaysWeek) || !$this->configRepository->isFeatureMaxPerWeekActive()) {
            return false;
        }

        return $this->playDateRepository->countByClownAvailabilityAndWeek($clownAvailability, $week) >= $softMaxPlaysWeek;
    }

    public function maxPlaysAndSubstitutionsWeekReached(Week $week, ClownAvailability $clownAvailability): bool
    {
        $softMaxPlaysAndSubstitutionsWeek = $clownAvailability->getSoftMaxPlaysAndSubstitutionsWeek();
        if (is_null($softMaxPlaysAndSubstitutionsWeek)  || !$this->configRepository->isFeatureMaxPerWeekActive()) {
            return false;
        }

        $substitutions = $this->substitutionRepository->byMonth($clownAvailability->getMonth());
        $substitutionsSameWeek = array_filter(
            $substitutions,
            fn (Substitution $substitution) => $week == $substitution->getWeek()
                && $substitution->getSubstitutionClown() === $clownAvailability->getClown()
        );

        $countPlayDatesSameWeek = $this->playDateRepository->countByClownAvailabilityAndWeek($clownAvailability, $week);

        return count($substitutionsSameWeek) + $countPlayDatesSameWeek >= $softMaxPlaysAndSubstitutionsWeek;
    }

    public function maxPlaysDayReached(DateTimeInterface $date, ClownAvailability $clownAvailability): bool
    {
        $playDates = $this->playDateRepository->confirmedByMonth($clownAvailability->getMonth());
        $playDatesSameDay = array_filter(
            $playDates,
            fn (PlayDate $playDate): bool => $date == $playDate->getDate()
                && $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );

        $substitutions = $this->substitutionRepository->byMonth($clownAvailability->getMonth());
        $substitutionsSameDay = array_filter(
            $substitutions,
            fn (Substitution $substitution): bool => $date == $substitution->getDate()
                && $substitution->getSubstitutionClown() === $clownAvailability->getClown()
        );

        return (count($playDatesSameDay) + count($substitutionsSameDay)) >= $clownAvailability->getMaxPlaysDay();
    }
}
