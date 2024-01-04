<?php

namespace App\ViewModel;

use App\Entity\Month;
use App\Value\ScheduleStatus;
use App\Value\TimeSlotPeriodInterface;

class Schedule
{
    private array $days = [];

    public function __construct(private ScheduleStatus $status, public readonly Month $month)
    {
    }

    public function isInProgress(): bool
    {
        return ScheduleStatus::IN_PROGRESS === $this->status;
    }

    public function isCompleted(): bool
    {
        return ScheduleStatus::COMPLETED === $this->status;
    }

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
