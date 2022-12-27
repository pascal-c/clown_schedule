<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;
use App\Repository\TimeSlotRepository;

class AvailabilityChecker
{
    public function __construct(private PlayDateRepository $playDateRepository, private TimeSlotRepository $timeSlotRepository)
    {
    }

    public function isAvailableOn(\DateTimeInterface $date, string $daytime, ClownAvailability $clownAvailability)
    {
        $playDates = $this->playDateRepository->byMonth($clownAvailability->getMonth());
        $playDatesSameTimeSlot = array_filter(
            $playDates,
            fn($playDate) => $date == $playDate->getDate() && 
                $daytime == $playDate->getDaytime() &&
                $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );

        return 
            $clownAvailability->isAvailableOn($date, $daytime) && 
            count($playDatesSameTimeSlot) == 0 &&
            !$this->isSubstitutionClown($date, $daytime, $clownAvailability->getClown())
        ;
    }

    public function isAvailableFor(PlayDate $playDate, ClownAvailability $clownAvailability): bool
    {
        return 
            $this->isAvailableOn($playDate->getDate(), $playDate->getDaytime(), $clownAvailability) && 
            !$this->maxPlaysMonthReached($clownAvailability) &&
            !$this->maxPlaysDayReached($playDate->getDate(), $clownAvailability) &&
            !$this->onlyMen($playDate, $clownAvailability)
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
            fn($playDate) => $date == $playDate->getDate() && 
                $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        );

        return count($playDatesSameDay) >= $clownAvailability->getMaxPlaysDay();
    }

    private function onlyMen(PlayDate $playDate, ClownAvailability $clownAvailability)
    {
        if ($playDate->getPlayingClowns()->count() != 1) {
            return false;
        }

        return $playDate->getPlayingClowns()->first()->getGender() == 'male' && 
            $clownAvailability->getClown()->getGender() == 'male';
    }

    private function isSubstitutionClown(\DateTimeInterface $date, string $daytime, Clown $clown)
    {
        $timeSlot = $this->timeSlotRepository->find($date, $daytime);
        if (is_null($timeSlot)) {
            return false;
        }

        return $timeSlot->getSubstitutionClown() === $clown;
    }
}
