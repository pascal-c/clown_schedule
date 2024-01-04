<?php

namespace App\ViewController;

use App\Entity\Month;
use App\Entity\Vacation;
use App\Repository\VacationRepository;
use App\ViewModel\Day;

class DayViewController
{
    private \IntlDateFormatter $dayShortNameFormatter;
    private \IntlDateFormatter $dayLongNameFormatter;
    private \IntlDateFormatter $dayNumberFormatter;

    public function __construct(private VacationRepository $vacationRepository)
    {
        $this->dayShortNameFormatter = new \IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'EEE'
        );
        $this->dayLongNameFormatter = new \IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'EEEE'
        );
        $this->dayNumberFormatter = new \IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'dd. LLL'
        );
    }

    public function getDay(\DateTimeImmutable $date): Day
    {
        return new Day(
            date: $date,
            dayShortName: $this->dayShortNameFormatter->format($date),
            dayLongName: $this->dayLongNameFormatter->format($date),
            dayNumber: $this->dayNumberFormatter->format($date),
            dayHolidayName: $this->getHolidayName($date),
            isWeekend: $this->isWeekend($date),
            isHoliday: $this->isHoliday($date),
            vacation: $this->getVacation($date)
        );
    }

    private function getVacation(\DateTimeImmutable $date): ?Vacation
    {
        foreach ($this->vacationRepository->byYear(new Month($date)) as $vacation) {
            if ($vacation->getStartDate() <= $date && $vacation->getEndDate() >= $date) {
                return $vacation;
            }
        }

        return null;
    }

    private function isWeekend(\DateTimeImmutable $date): bool
    {
        return $date->format('N') >= 6;
    }

    private function isHoliday(\DateTimeImmutable $date): bool
    {
        return array_key_exists($date->format('Y-m-d'), $this->holidaysForYear($date->format('Y')));
    }

    private function holidaysForYear(string $year): array
    {
        $easterDate = \DateTimeImmutable::createFromFormat('U', easter_date($year))
            ->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $busAndBedDate = (new \DateTimeImmutable($year.'-11-23'))->modify('last Wednesday');

        return [
            $year.'-01-01' => 'Neujahr', // new year
            $year.'-05-01' => 'Tag der Arbeit', // day of work!
            $year.'-10-03' => 'Tag der deutschen Einheit', // reunion day
            $year.'-10-31' => 'Reformationstag', // reformation day
            $year.'-12-25' => '1. Weihnachtsfeiertag', // chrismas 1
            $year.'-12-26' => '2. Weihnachtsfeiertag', // chrismas 2
            $easterDate->format('Y-m-d') => 'Ostersonntag', // easter
            $easterDate->modify('-2 days')->format('Y-m-d') => 'Karfreitag', // easter friday
            $easterDate->modify('+1 day')->format('Y-m-d') => 'Ostermontag', // easter monday
            $easterDate->modify('+39 day')->format('Y-m-d') => 'Himmelfahrt', // trip to heaven
            $easterDate->modify('+50 day')->format('Y-m-d') => 'Pfingsten', // pentercote
            $busAndBedDate->format('Y-m-d') => 'Buß- und Bettag', // bus and bed day
        ];
    }

    private function getHolidayName(\DateTimeImmutable $date)
    {
        return $this->isHoliday($date)
        ? $this->holidaysForYear($date->format('Y'))[$date->format('Y-m-d')]
        : null;
    }
}
