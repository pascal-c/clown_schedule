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
        $this->assertEquals('Mon', $day->getDayShortName());
    }

    /**
     * @depends testgetDayNumber
     */
    public function testgetEntries(Day $day): void
    {
        $day->addEntry(Daytime::AM, 'am first');
        $day->addEntry(Daytime::AM, 'am second');
        $this->assertEquals(['am first', 'am second'], $day->getEntries(Daytime::AM));
        $this->assertEquals([], $day->getEntries(Daytime::PM));

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
}
