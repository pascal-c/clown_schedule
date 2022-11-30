<?php

namespace App\Tests\ViewModel;

use PHPUnit\Framework\TestCase;
use App\Entity\Daytime;
use App\Entity\Month;
use App\ViewModel\Schedule;

final class ScheduleTest extends TestCase
{
    public function testgetDays(): void
    {
        $month = new Month(new \DateTimeImmutable('2022-08'));
        $schedule = new Schedule($month);
        $schedule->add(new \DateTimeImmutable('2022-08-23'), Daytime::AM, '23. am first entry');
        $schedule->add(new \DateTimeImmutable('2022-08-23'), Daytime::AM, '23. am second entry');
        $schedule->add(new \DateTimeImmutable('2022-08-31'), Daytime::PM, '31. pm entry');
        $days = $schedule->getDays();
        $this->assertEquals(31, count($days));

        $twentythird = $days[22];
        $this->assertEquals('23', $twentythird->getDayNumber());
        $this->assertEquals(['23. am first entry', '23. am second entry'], $twentythird->getEntries(Daytime::AM));
        $this->assertEquals([], $twentythird->getEntries(Daytime::PM));

        $thirtyfirst = $days[30];
        $this->assertEquals('31', $thirtyfirst->getDayNumber());
        $this->assertEquals([], $thirtyfirst->getEntries(Daytime::AM));
        $this->assertEquals(['31. pm entry'], $thirtyfirst->getEntries(Daytime::PM));
    }
}
