<?php

namespace App\Tests\ViewController;

use PHPUnit\Framework\TestCase;
use App\Entity\Daytime;
use App\Repository\VacationRepository;
use App\ViewController\DayViewController;
use App\ViewModel\Day;

final class DayViewControllerTest extends TestCase
{
    public function testgetDayShortName(): void
    {
        $vacationRepository = $this->createMock(VacationRepository::class);
        $viewController = new DayViewController($vacationRepository);
        $date = new \DateTimeImmutable('2022-08-08');
        $day = $viewController->getDay($date);
        
        $this->assertEquals('Mo.', $day->getDayShortName());
    }

    /**
     * @dataProvider isWeekendProvider
     */
    public function testisWeekend(\DateTimeImmutable $date, bool $expectedResult): void
    {   
        $vacationRepository = $this->createMock(VacationRepository::class);
        $viewController = new DayViewController($vacationRepository);
        $day = $viewController->getDay($date);
        
        $this->assertEquals($expectedResult, $day->isWeekend());
    }

    public function isWeekendProvider(): array
    {
        return [
            [new \DateTimeImmutable('2022-08-05'), false], // Friday
            [new \DateTimeImmutable('2022-08-06'), true], // Saturday
            [new \DateTimeImmutable('2022-08-07'), true], // Sunday
            [new \DateTimeImmutable('2022-08-08'), false], // Monday
        ];
    }

    /**
     * @dataProvider isHolidayProvider
     */
    public function testisHoliday(\DateTimeImmutable $date, bool $expectedResult): void
    {
        $vacationRepository = $this->createMock(VacationRepository::class);
        $viewController = new DayViewController($vacationRepository);
        $day = $viewController->getDay($date);
        
        $this->assertEquals($expectedResult, $day->isHoliday());
    }

    public function isHolidayProvider(): array
    {
        return [

            [new \DateTimeImmutable('2022-01-01'), true], // new year
            [new \DateTimeImmutable('2022-01-02'), false], // no holiday
            [new \DateTimeImmutable('1974-04-12'), true], // Easter Friday
            [new \DateTimeImmutable('1974-04-13'), false], // no holiday - Easter Saturday
            [new \DateTimeImmutable('1974-04-14'), true], // Easter Sunday
            [new \DateTimeImmutable('1974-04-15'), true], // Easter Monday
            [new \DateTimeImmutable('2023-05-18'), true], // trip to heaven
            [new \DateTimeImmutable('2023-05-29'), true], // Pentecost
            [new \DateTimeImmutable('2019-05-01'), true], // day of work!
            [new \DateTimeImmutable('2020-10-03'), true], // reunion day
            [new \DateTimeImmutable('2021-10-31'), true], // reformation day
            [new \DateTimeImmutable('2022-11-16'), true], // bed and bus day
            [new \DateTimeImmutable('2023-12-25'), true], // chrismas 1
            [new \DateTimeImmutable('2024-12-26'), true], // chrismas 2
            [new \DateTimeImmutable('2024-12-31'), false], // no holiday
        ];
    }
}
