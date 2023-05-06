<?php

namespace App\ViewModel;

use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;

class Schedule
{
    private array $days = [];

    public function add(TimeSlotInterface $timeSlot, string $key, mixed $entry): void
    {
        $date = $timeSlot->getDate()->format('d');
        if (TimeSlotPeriodInterface::ALL === $timeSlot->getDaytime()) {
            $this->days[$date]->addEntry(TimeSlotInterface::AM, $key, $entry);
            $this->days[$date]->addEntry(TimeSlotInterface::PM, $key, $entry);
        } else {
            $this->days[$date]->addEntry($timeSlot->getDaytime(), $key, $entry);
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
