<?php

namespace App\ViewModel;

use App\Entity\Daytime;
use IntlDateFormatter;

class Day
{
    private array $entriesAm = [];
    private array $entriesPm = [];
    private IntlDateFormatter $dayShortNameFormatter;
    private IntlDateFormatter $dayLongNameFormatter;

    public function __construct(private \DateTimeInterface $date)
    {
        $this->dayShortNameFormatter = new IntlDateFormatter(
            'de_DE', 
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Europe/Berlin',
            \IntlDateFormatter::GREGORIAN,
            'EEE');
        $this->dayLongNameFormatter = new IntlDateFormatter(
            'de_DE', 
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Europe/Berlin',
            \IntlDateFormatter::GREGORIAN,
            'EEEE');
    }
    
    public function addEntry(string $daytime, string $key, mixed $entry)
    {
        if ($daytime == Daytime::AM) {
            $this->entriesAm[$key][] = $entry;
        } elseif ($daytime == Daytime::PM) {
            $this->entriesPm[$key][] = $entry;
        } else {
            throw new \InvalidArgumentException('this is not a valid daytime');
        }
    }

    public function getEntries(string $daytime, string $key): array
    {
        if ($daytime == Daytime::AM) {
            return array_key_exists($key, $this->entriesAm) ? $this->entriesAm[$key] : [];
        } elseif ($daytime == Daytime::PM) {
            return array_key_exists($key, $this->entriesPm) ? $this->entriesPm[$key] : [];
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
        return $this->dayShortNameFormatter->format($this->date);
    }

    public function getDayName(): string
    {
        return $this->isHolyday() ? $this->holidaysForYear()[$this->getDateString()] : $this->dayLongNameFormatter->format($this->date);
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDateString(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function isFree(): bool
    {
        return $this->isWeekend() || $this->isHolyday();
    }

    public function isWeekend(): bool
    {
        return $this->date->format('N') >= 6;
    }

    public function isHolyday(): bool
    {
        return array_key_exists($this->getDateString(), $this->holidaysForYear());
    }

    private function holidaysForYear(): array
    {
        $year = $this->date->format('Y');
        
        $easterDate = \DateTimeImmutable::createFromFormat('U', easter_date($year))
            ->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $busAndBedDate = (new \DateTimeImmutable($year . '-11-23'))->modify('last Wednesday');

        return [
            $year . '-01-01' => 'Neujahr', // new year
            $year . '-05-01' => 'Tag der Arbeit', // day of work!
            $year . '-10-03' => 'Tag der deutschen Einheit', // reunion day
            $year . '-10-31' => 'Reformationstag', // reformation day
            $year . '-12-25' => '1. Weihnachtsfeiertag', // chrismas 1
            $year . '-12-26' => '2. Weihnachtsfeiertag', // chrismas 2
            $easterDate->format('Y-m-d') => 'Ostersonntag', // easter
            $easterDate->modify('-2 days')->format('Y-m-d') => 'Karfreitag', // easter friday
            $easterDate->modify('+1 day')->format('Y-m-d') => 'Ostermontag', // easter monday
            $easterDate->modify('+39 day')->format('Y-m-d') => 'Himmelfahrt', // trip to heaven
            $easterDate->modify('+50 day')->format('Y-m-d') => 'Pfingsten', // pentercote
            $busAndBedDate->format('Y-m-d') => 'Buß- und Bettag', // bus and bed day
        ];
    }
}
