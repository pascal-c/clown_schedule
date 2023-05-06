<?php

declare(strict_types=1);

namespace App\Value;

use App\Lib\Collection;
use DateTimeImmutable;
use InvalidArgumentException;

class TimeSlotPeriod implements TimeSlotPeriodInterface
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

    public static function getDaytimeOptions(): Collection
    {
        return new Collection(static::DAYTIMES);
    }

    public function __construct(private DateTimeImmutable $date, private string $daytime) 
    {
        if (!static::getDaytimeOptions()->contains($daytime)) {
            throw new InvalidArgumentException($daytime . ' is not a valid daytime');
        } 
    }
    
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function getDaytime(): ?string
    {
        return $this->daytime;
    }
}
