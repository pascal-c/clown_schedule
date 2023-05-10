<?php

namespace App\ViewModel;

use App\Value\TimeSlotPeriodInterface;

class Schedule
{
    private array $days = [];

    public function add(TimeSlotPeriodInterface $timeSlotPeriod, string $key, mixed $entry): void
    {
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            $this->days[$timeSlot->getDate()->format('d')]->addEntry($timeSlot->getDaytime(), $key, $entry);
        }
    }

    public function getDays(): array
    {
        return array_values($this->days);
    }

    public function setDays(array $days): self
    {
        $this->days = $days;

        return $this;
    }
}
