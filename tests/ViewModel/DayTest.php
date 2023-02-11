<?php

namespace App\Tests\ViewModel;

use PHPUnit\Framework\TestCase;
use App\Entity\Daytime;
use App\ViewModel\Day;

final class DayTest extends TestCase
{
    public function testgetDayNumber(): Day
    {
        $day = new Day(
            date: new \DateTimeImmutable('2022-08-08'),
            dayLongName: 'Freitag',
            dayShortName: 'Fr.',
            dayHolidayName: 'Himmelfahrt',
            isWeekend: false,
            isHoliday: true,
            vacation: null,
        );
        $this->assertEquals('08', $day->getDayNumber());
        
        return $day;
    }

    /**
     * @depends testgetDayNumber
     */
    public function testgetDayShortName(Day $day): void
    {
        $this->assertEquals('Fr.', $day->getDayShortName());
    }

    /**
     * @depends testgetDayNumber
     */
    public function testgetEntries(Day $day): void
    {
        $day->addEntry(Daytime::AM, 'key1', 'am key1 first');
        $day->addEntry(Daytime::AM, 'key1', 'am key1 second');
        $day->addEntry(Daytime::AM, 'key2', 'am key2');
        $this->assertEquals(['am key1 first', 'am key1 second'], $day->getEntries(Daytime::AM, 'key1'));
        $this->assertEquals(['am key2'], $day->getEntries(Daytime::AM, 'key2'));
        $this->assertEquals([], $day->getEntries(Daytime::PM, 'key1'));
        $this->assertEquals([], $day->getEntries(Daytime::PM, 'key2'));
    }

    /**
     * @depends testgetDayNumber
     */
    public function testisWeekend(Day $day): void
    {   
        $this->assertEquals(false, $day->isWeekend());
    }

    /**
     * @depends testgetDayNumber
     */
    public function testisHoliday(Day $day): void
    {
        $this->assertEquals(true, $day->isHoliday());
    }

    /**
     * @depends testgetDayNumber
     */
    public function testisVacation(Day $day): void
    {
        $this->assertEquals(false, $day->isVacation());
    }
}
