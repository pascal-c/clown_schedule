<?php

namespace App\Tests\ViewModel;

use App\Entity\Daytime;
use App\Entity\Month;
use App\Value\ScheduleStatus;
use App\Value\TimeSlotPeriod;
use App\ViewModel\Day;
use App\ViewModel\Schedule;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class ScheduleTest extends TestCase
{
    public function testgetDays(): void
    {
        $month = Month::build('2022-09');
        $schedule = new Schedule(ScheduleStatus::IN_PROGRESS, $month);
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
        $this->assertEquals('2022-08-23', $twentythird->getDateString());
        $this->assertEquals(['23. am first entry', '23. am second entry'], $twentythird->getEntries(Daytime::AM, 'key'));
        $this->assertEquals([], $twentythird->getEntries(Daytime::PM, 'key'));

        $thirtyfirst = $days[1];
        $this->assertEquals('2022-08-31', $thirtyfirst->getDateString());
        $this->assertEquals([], $thirtyfirst->getEntries(Daytime::AM, 'key'));
        $this->assertEquals(['31. pm entry'], $thirtyfirst->getEntries(Daytime::PM, 'key'));

        $this->assertTrue($schedule->isInProgress());
        $this->assertFalse($schedule->isCompleted());
        $this->assertSame($month, $schedule->month);
    }

    private function buildDay(DateTimeImmutable $date): Day
    {
        return new Day(
            date: $date,
            dayLongName: 'Freitag',
            dayShortName: 'Fr.',
            dayNumber: '06. Mai',
            dayHolidayName: 'Himmelfahrt',
            isWeekend: false,
            vacation: null,
        );
    }
}
