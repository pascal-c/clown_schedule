<?php

namespace App\Tests\ViewModel;

use PHPUnit\Framework\TestCase;
use App\Entity\Daytime;
use App\Entity\Month;
use App\Value\TimeSlotPeriod;
use App\ViewModel\Day;
use App\ViewModel\Schedule;
use DateTimeImmutable;

final class ScheduleTest extends TestCase
{
    public function testgetDays(): void
    {
        $month = new Month(new \DateTimeImmutable('2022-08'));
        $schedule = new Schedule($month);
        $schedule->setDays([
            '23' => $this->buildDay(new DateTimeImmutable('2022-08-23')),
            '31' => $this->buildDay(new DateTimeImmutable('2022-08-31')),
        ]);
        $timeSlot1 = new TimeSlotPeriod(new DateTimeImmutable('2022-08-23'), Daytime::AM);
        $timeSlot2 = new TimeSlotPeriod(new DateTimeImmutable('2022-08-31'), Daytime::PM);
        $schedule->add($timeSlot1, 'key', '23. am first entry');
        $schedule->add($timeSlot1, 'key', '23. am second entry');
        $schedule->add($timeSlot2, 'key', '31. pm entry');
        $days = $schedule->getDays();
        $this->assertEquals(2, count($days));

        $twentythird = $days[0];
        $this->assertEquals('23', $twentythird->getDayNumber());
        $this->assertEquals(['23. am first entry', '23. am second entry'], $twentythird->getEntries(Daytime::AM, 'key'));
        $this->assertEquals([], $twentythird->getEntries(Daytime::PM, 'key'));

        $thirtyfirst = $days[1];
        $this->assertEquals('31', $thirtyfirst->getDayNumber());
        $this->assertEquals([], $thirtyfirst->getEntries(Daytime::AM, 'key'));
        $this->assertEquals(['31. pm entry'], $thirtyfirst->getEntries(Daytime::PM, 'key'));
    }

    private function buildDay(DateTimeImmutable $date): Day
    {
        return new Day(
            date: $date,
            dayLongName: 'Freitag',
            dayShortName: 'Fr.',
            dayHolidayName: 'Himmelfahrt',
            isWeekend: false,
            isHoliday: true,
            vacation: null,
        );
    }
}
