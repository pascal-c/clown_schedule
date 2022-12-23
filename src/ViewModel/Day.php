<?php

namespace App\ViewModel;

use App\Entity\Daytime;

class Day
{
    private array $entriesAm = [];
    private array $entriesPm = [];

    public function __construct(private \DateTimeInterface $date)
    {
    }
    
    public function addEntry(string $daytime, $entry)
    {
        if ($daytime == Daytime::AM) {
            $this->entriesAm[] = $entry;
        } elseif ($daytime == Daytime::PM) {
            $this->entriesPm[] = $entry;
        } else {
            throw new \InvalidArgumentException('this is not a valid daytime');
        }
    }

    public function getEntries(string $daytime): array
    {
        if ($daytime == Daytime::AM) {
            return $this->entriesAm;
        } elseif ($daytime == Daytime::PM) {
            return $this->entriesPm;
        } else {
            throw new \InvalidArgumentException('this is not a valid daytime');
        }
    }

    public function getDayNumber(): string
    {
        return $this->date->format('d');
    }

    public function getDayShortName(): string
    {
        return $this->date->format('D');
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDateString(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function isWeekend(): bool
    {
        return $this->date->format('N') >= 6;
    }
}
