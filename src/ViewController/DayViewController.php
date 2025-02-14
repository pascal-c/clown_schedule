<?php

namespace App\ViewController;

use App\Entity\Month;
use App\Entity\Vacation;
use App\Repository\HolidayRepository;
use App\Repository\VacationRepository;
use App\ViewModel\Day;
use DateTimeImmutable;
use IntlDateFormatter;

class DayViewController
{
    private IntlDateFormatter $dayShortNameFormatter;
    private IntlDateFormatter $dayLongNameFormatter;
    private IntlDateFormatter $dayNumberFormatter;

    public function __construct(private VacationRepository $vacationRepository, private HolidayRepository $holidayRepository)
    {
        $this->dayShortNameFormatter = new IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'EEE'
        );
        $this->dayLongNameFormatter = new IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'EEEE'
        );
        $this->dayNumberFormatter = new IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'dd. LLL'
        );
    }

    public function getDay(DateTimeImmutable $date): Day
    {
        return new Day(
            date: $date,
            dayShortName: $this->dayShortNameFormatter->format($date),
            dayLongName: $this->dayLongNameFormatter->format($date),
            dayNumber: $this->dayNumberFormatter->format($date),
            dayHolidayName: $this->holidayRepository->oneByDate($date),
            isWeekend: $this->isWeekend($date),
            vacation: $this->getVacation($date)
        );
    }

    private function getVacation(DateTimeImmutable $date): ?Vacation
    {
        foreach ($this->vacationRepository->byYear((new Month($date))->getYear()) as $vacation) {
            if ($vacation->getStartDate() <= $date && $vacation->getEndDate() >= $date) {
                return $vacation;
            }
        }

        return null;
    }

    private function isWeekend(DateTimeImmutable $date): bool
    {
        return $date->format('N') >= 6;
    }
}
