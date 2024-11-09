<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Entity\Week;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Value\TimeSlot;
use App\Value\TimeSlotPeriodInterface;
use DateTimeInterface;

class AvailabilityChecker
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private ConfigRepository $configRepository,
    ) {
    }

    public function isAvailableOn(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability): bool
    {
        return
            $clownAvailability->isAvailableOn($timeSlotPeriod)
            && !$this->isPlayingClownWithin($timeSlotPeriod, $clownAvailability->getClown())
            && !$this->isSubstitutionClownWithin($timeSlotPeriod, $clownAvailability->getClown())
        ;
    }

    public function isAvailableFor(PlayDate $playDate, ClownAvailability $clownAvailability): bool
    {
        return
            $this->isAvailableOn($playDate, $clownAvailability)
            && !$this->isBlocked($playDate->getVenue(), $clownAvailability->getClown())
            && !$this->maxPlaysMonthReached($clownAvailability)
            && !$this->maxPlaysDayReached($playDate->getDate(), $clownAvailability)
            && !$this->onlyMen($playDate, $clownAvailability)
        ;
    }

    public function isAvailableForSubstitution(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability): bool
    {
        return
            $this->isAvailableOn($timeSlotPeriod, $clownAvailability)
            && !$this->maxSubstitutionsMonthReached($clownAvailability)
        ;
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
        if (is_null($softMaxPlaysWeek) || !$this->configRepository->hasFeatureMaxPerWeek()) {
            return false;
        }

        return $this->playDateRepository->countByClownAvailabilityAndWeek($clownAvailability, $week) >= $softMaxPlaysWeek;
    }

    public function maxPlaysAndSubstitutionsWeekReached(Week $week, ClownAvailability $clownAvailability): bool
    {
        $softMaxPlaysAndSubstitutionsWeek = $clownAvailability->getSoftMaxPlaysAndSubstitutionsWeek();
        if (is_null($softMaxPlaysAndSubstitutionsWeek)  || !$this->configRepository->hasFeatureMaxPerWeek()) {
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
        $playDates = $this->playDateRepository->byMonth($clownAvailability->getMonth());
        $playDatesSameDay = array_filter(
            $playDates,
            fn ($playDate) => $date == $playDate->getDate()
                && $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );

        return count($playDatesSameDay) >= $clownAvailability->getMaxPlaysDay();
    }

    private function onlyMen(PlayDate $playDate, ClownAvailability $clownAvailability): bool
    {
        if (1 != $playDate->getPlayingClowns()->count()) {
            return false;
        }

        return 'male' == $playDate->getPlayingClowns()->first()->getGender()
            && 'male' == $clownAvailability->getClown()->getGender();
    }

    private function isSubstitutionClownWithin(TimeSlotPeriodInterface $timeSlotPeriod, Clown $clown): bool
    {
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            $substitution = $this->substitutionRepository->find($timeSlot->getDate(), $timeSlot->getDaytime());
            if (!is_null($substitution) && $substitution->getSubstitutionClown() === $clown) {
                return true;
            }
        }

        return false;
    }

    private function isPlayingClownWithin(TimeSlotPeriodInterface $timeSlotPeriod, Clown $clown): bool
    {
        $playDates = $this->playDateRepository->byMonth($timeSlotPeriod->getMonth());
        $playDatesSameTimeSlot = array_filter(
            $playDates,
            fn ($playDate) => $playDate->getPlayingClowns()->contains($clown)
                && array_reduce(
                    $timeSlotPeriod->getTimeSlots(),
                    fn (bool $carry, TimeSlot $timeSlot) => $carry || !empty(array_filter($playDate->getTimeSlots(), fn (TimeSlot $playDateTimeSlot) => $timeSlot->getDate() == $playDateTimeSlot->getDate() && $timeSlot->getDaytime() == $playDateTimeSlot->getDaytime())),
                    false
                )
        );

        return count($playDatesSameTimeSlot) > 0;
    }

    public function isBlocked(?Venue $venue, Clown $clown): bool
    {
        return is_null($venue) || $venue->getBlockedClowns()->contains($clown);
    }
}
