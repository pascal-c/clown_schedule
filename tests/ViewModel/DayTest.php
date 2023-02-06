<?php

namespace App\Tests\ViewModel;

use PHPUnit\Framework\TestCase;
use App\Entity\Daytime;
use App\ViewModel\Day;

final class DayTest extends TestCase
{
    public function testgetDayNumber(): Day
    {
        $day = new Day(new \DateTimeImmutable('2022-08-08'));
        $this->assertEquals('08', $day->getDayNumber());

        return $day;
    }

    /**
     * @depends testgetDayNumber
     */
    public function testgetDayShortName(Day $day): void
    {
        $this->assertEquals('Mo.', $day->getDayShortName());
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
     * @dataProvider isWeekendProvider
     */
    public function testisWeekend(Day $day, bool $expectedResult): void
    {   
        $this->assertEquals($expectedResult, $day->isWeekend());
    }

    public function isWeekendProvider(): array
    {
        return [
            [new Day(new \DateTimeImmutable('2022-08-05')), false], // Friday
            [new Day(new \DateTimeImmutable('2022-08-06')), true], // Saturday
            [new Day(new \DateTimeImmutable('2022-08-07')), true], // Sunday
            [new Day(new \DateTimeImmutable('2022-08-08')), false], // Monday
        ];
    }

    /**
     * @dataProvider isHolidayProvider
     */
    public function testisHolyday(Day $day, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $day->isHolyday());
    }

    public function isHolidayProvider(): array
    {
        return [

            [new Day(new \DateTimeImmutable('2022-01-01')), true], // new year
            [new Day(new \DateTimeImmutable('2022-01-02')), false], // no holiday
            [new Day(new \DateTimeImmutable('1974-04-12')), true], // Easter Friday
            [new Day(new \DateTimeImmutable('1974-04-13')), false], // no holiday - Easter Saturday
            [new Day(new \DateTimeImmutable('1974-04-14')), true], // Easter Sunday
            [new Day(new \DateTimeImmutable('1974-04-15')), true], // Easter Monday
            [new Day(new \DateTimeImmutable('2023-05-18')), true], // trip to heaven
            [new Day(new \DateTimeImmutable('2023-05-29')), true], // Pentecost
            [new Day(new \DateTimeImmutable('2019-05-01')), true], // day of work!
            [new Day(new \DateTimeImmutable('2020-10-03')), true], // reunion day
            [new Day(new \DateTimeImmutable('2021-10-31')), true], // reformation day
            [new Day(new \DateTimeImmutable('2022-11-16')), true], // bed and bus day
            [new Day(new \DateTimeImmutable('2023-12-25')), true], // chrismas 1
            [new Day(new \DateTimeImmutable('2024-12-26')), true], // chrismas 2
            [new Day(new \DateTimeImmutable('2024-12-31')), false], // no holiday
        ];
    }
}
