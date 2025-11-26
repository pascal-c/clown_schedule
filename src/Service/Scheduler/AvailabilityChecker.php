<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker\MaxPlaysReachedChecker;
use App\Value\TimeSlot;
use App\Value\TimeSlotPeriodInterface;

class AvailabilityChecker
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private MaxPlaysReachedChecker $maxPlaysReachedChecker,
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
            && !$this->maxPlaysReachedChecker->maxPlaysMonthReached($clownAvailability)
            && !$this->maxPlaysReachedChecker->maxPlaysDayReached($playDate->getDate(), $clownAvailability)
            && !$this->onlyMen($playDate, $clownAvailability)
        ;
    }

    public function isAvailableForSubstitution(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability): bool
    {
        return
            $this->isAvailableOn($timeSlotPeriod, $clownAvailability)
            && !$this->maxPlaysReachedChecker->maxSubstitutionsMonthReached($clownAvailability)
            && !$this->maxPlaysReachedChecker->maxPlaysDayReached($timeSlotPeriod->getDate(), $clownAvailability)
        ;
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
        $playDates = $this->playDateRepository->confirmedByMonth($timeSlotPeriod->getMonth());
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
