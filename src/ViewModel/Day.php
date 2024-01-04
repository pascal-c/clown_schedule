<?php

namespace App\ViewModel;

use App\Entity\Daytime;
use App\Entity\Vacation;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class Day
{
    private array $entriesAm = [];
    private array $entriesPm = [];

    public function __construct(
        private DateTimeInterface $date,
        private string $dayLongName,
        private string $dayShortName,
        private string $dayNumber,
        private ?string $dayHolidayName,
        private bool $isWeekend,
        private bool $isHoliday,
        private ?Vacation $vacation,
    ) {
    }

    public function addEntry(string $daytime, string $key, mixed $entry)
    {
        if (Daytime::AM === $daytime) {
            $this->entriesAm[$key][] = $entry;
        } elseif (Daytime::PM === $daytime) {
            $this->entriesPm[$key][] = $entry;
        } else {
            throw new InvalidArgumentException('this is not a valid daytime');
        }
    }

    public function getEntries(string $daytime, string $key): array
    {
        if (Daytime::AM == $daytime) {
            return array_key_exists($key, $this->entriesAm) ? $this->entriesAm[$key] : [];
        } elseif (Daytime::PM == $daytime) {
            return array_key_exists($key, $this->entriesPm) ? $this->entriesPm[$key] : [];
        } else {
            throw new InvalidArgumentException('this is not a valid daytime');
        }
    }

    public function getDayNumber(): string
    {
        return $this->dayNumber;
    }

    public function getDayShortName(): string
    {
        return $this->dayShortName;
    }

    public function getDayName(): string
    {
        return $this->isHoliday() ? $this->dayHolidayName : $this->dayLongName;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getDateString(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function isFree(): bool
    {
        return $this->isWeekend() || $this->isHoliday();
    }

    public function isWeekend(): bool
    {
        return $this->isWeekend;
    }

    public function isHoliday(): bool
    {
        return $this->isHoliday;
    }

    public function isVacation(): bool
    {
        return null != $this->vacation;
    }

    public function getVacationName(): ?string
    {
        return $this->isVacation() ? $this->vacation->getName() : null;
    }
}
