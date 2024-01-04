<?php

declare(strict_types=1);

namespace App\Value;

use App\Entity\Month;

trait TimeSlotPeriodTrait
{
    public function getTimeSlots(): array
    {
        if (TimeSlotPeriodInterface::ALL === $this->getDaytime()) {
            return [
                new TimeSlot($this->getDate(), TimeSlotInterface::AM),
                new TimeSlot($this->getDate(), TimeSlotInterface::PM),
            ];
        }

        return [new TimeSlot($this->getDate(), $this->getDaytime())];
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function getDaytime(): ?string
    {
        return $this->daytime;
    }

    public function getMonth(): Month
    {
        return new Month($this->date);
    }
}
