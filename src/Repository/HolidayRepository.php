<?php

declare(strict_types=1);

namespace App\Repository;

use DateTimeImmutable;
use DateTimeZone;

class HolidayRepository
{
    private array $cache = [];

    public function __construct(private ConfigRepository $configRepository)
    {
    }

    public function oneByDate(DateTimeImmutable $date): ?string
    {
        $year = $date->format('Y');

        $holidays = $this->cache[$year] ?? $this->cache[$year] = $this->byYear($year);

        return $holidays[$date->format('Y-m-d')] ?? null;
    }

    private function byYear(string $year): array
    {
        $federalState = $this->configRepository->getFederalState();
        $easterDate = DateTimeImmutable::createFromFormat('U', (string) easter_date((int) $year))
            ->setTimezone(new DateTimeZone('Europe/Berlin'));
        $busAndBedDate = (new DateTimeImmutable($year.'-11-23'))->modify('last Wednesday');

        return [
            $year.'-01-01' => 'Neujahr',                                                            // new year
            $year.'-01-06' => in_array($federalState, ['BW', 'BY', 'ST']) ? 'Drei Könige' : null,   // holy three kings
            $year.'-03-08' => 'BE' === $federalState ? 'Frauentag' : null,                          // womens day
            $year.'-05-01' => 'Tag der Arbeit',                                                     // day of work!
            $year.'-08-15' => in_array($federalState, ['BY', 'SL']) ? 'Mariä Himmelfahrt' : null,   // Maria's trip to heaven
            $year.'-10-03' => 'Tag der deutschen Einheit',                                          // reunion day
            $year.'-10-31' => in_array($federalState, ['BB', 'HB', 'HH', 'MV', 'NI', 'SN', 'ST', 'SH', 'TH']) ? 'Reformationstag' : null, // reformation day
            $year.'-11-01' => in_array($federalState, ['BW', 'BY', 'NW', 'RP', 'SL']) ? 'Allerheiligen' : null, // all saints
            $year.'-12-25' => '1. Weihnachtsfeiertag',                                              // chrismas 1
            $year.'-12-26' => '2. Weihnachtsfeiertag',                                              // chrismas 2
            $easterDate->format('Y-m-d') => 'Ostersonntag',                                         // easter
            $easterDate->modify('-2 days')->format('Y-m-d') => 'Karfreitag',                        // easter friday
            $easterDate->modify('+1 day')->format('Y-m-d') => 'Ostermontag',                        // easter monday
            $easterDate->modify('+39 day')->format('Y-m-d') => 'Himmelfahrt',                       // trip to heaven
            $easterDate->modify('+50 day')->format('Y-m-d') => 'Pfingsten',                         // pentercote
            $easterDate->modify('+60 day')->format('Y-m-d') => in_array($federalState, ['BW', 'BY', 'HE', 'NW', 'RP', 'SL']) ? 'Fronleichnam' : null, // Corpus Christi
            $busAndBedDate->format('Y-m-d') => 'SN' === $federalState ? 'Buß- und Bettag' : null,    // bus and bed day
        ];
    }
}
