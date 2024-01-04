<?php

declare(strict_types=1);

namespace App\Value;

use App\Entity\Month;
use App\Lib\Collection;

class TimeSlot implements TimeSlotInterface
{
    public static function getDaytimeOptions(): Collection
    {
        return new Collection(static::DAYTIMES);
    }

    public function __construct(private \DateTimeImmutable $date, private string $daytime)
    {
        if (!static::getDaytimeOptions()->contains($daytime)) {
            throw new \InvalidArgumentException($daytime.' is not a valid daytime');
        }
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
