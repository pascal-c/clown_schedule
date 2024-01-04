<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Value\TimeSlot;
use App\Value\TimeSlotPeriodInterface;

class AvailabilityChecker
{
    public function __construct(private PlayDateRepository $playDateRepository, private SubstitutionRepository $substitutionRepository)
    {
    }

    public function isAvailableOn(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability)
    {
        $playDates = $this->playDateRepository->byMonth($clownAvailability->getMonth());
        $playDatesSameTimeSlot = array_filter(
            $playDates,
            fn ($playDate) => $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
                && array_reduce(
                    $timeSlotPeriod->getTimeSlots(),
                    fn (bool $carry, TimeSlot $timeSlot) => $carry || !empty(array_filter($playDate->getTimeSlots(), fn (TimeSlot $playDateTimeSlot) => $timeSlot->getDate() == $playDateTimeSlot->getDate() && $timeSlot->getDaytime() == $playDateTimeSlot->getDaytime())),
                    false
                )
        );

        return
            $clownAvailability->isAvailableOn($timeSlotPeriod)
            && 0 == count($playDatesSameTimeSlot)
            && !$this->isSubstitutionClownWithin($timeSlotPeriod, $clownAvailability->getClown())
        ;
    }

    public function isAvailableFor(PlayDate $playDate, ClownAvailability $clownAvailability): bool
    {
        return
            $this->isAvailableOn($playDate, $clownAvailability)
            && !$this->maxPlaysMonthReached($clownAvailability)
            && !$this->maxPlaysDayReached($playDate->getDate(), $clownAvailability)
            && !$this->onlyMen($playDate, $clownAvailability)
        ;
    }

    public function maxPlaysMonthReached(ClownAvailability $clownAvailability)
    {
        return $clownAvailability->getCalculatedPlaysMonth() >= $clownAvailability->getMaxPlaysMonth();
    }

    public function maxPlaysDayReached(\DateTimeInterface $date, ClownAvailability $clownAvailability)
    {
        $playDates = $this->playDateRepository->byMonth($clownAvailability->getMonth());
        $playDatesSameDay = array_filter(
            $playDates,
            fn ($playDate) => $date == $playDate->getDate()
                && $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );

        return count($playDatesSameDay) >= $clownAvailability->getMaxPlaysDay();
    }

    private function onlyMen(PlayDate $playDate, ClownAvailability $clownAvailability)
    {
        if (1 != $playDate->getPlayingClowns()->count()) {
            return false;
        }

        return 'male' == $playDate->getPlayingClowns()->first()->getGender()
            && 'male' == $clownAvailability->getClown()->getGender();
    }

    private function isSubstitutionClownWithin(TimeSlotPeriodInterface $timeSlotPeriod, Clown $clown)
    {
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            $substitution = $this->substitutionRepository->find($timeSlot->getDate(), $timeSlot->getDaytime());
            if (!is_null($substitution) && $substitution->getSubstitutionClown() === $clown) {
                return true;
            }
        }

        return false;
    }
}
