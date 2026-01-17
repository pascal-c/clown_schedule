<?php

namespace App\Tests\ViewController;

use App\Entity\Vacation;
use App\Repository\HolidayRepository;
use App\Repository\VacationRepository;
use App\ViewController\DayViewController;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[AllowMockObjectsWithoutExpectations]
final class DayViewControllerTest extends TestCase
{
    private VacationRepository&MockObject $vacationRepository;
    private HolidayRepository&MockObject $holidayRepository;
    private DayViewController $viewController;

    public function setUp(): void
    {
        $this->vacationRepository = $this->createMock(VacationRepository::class);
        $this->holidayRepository = $this->createMock(HolidayRepository::class);
        $this->viewController = new DayViewController($this->vacationRepository, $this->holidayRepository);
    }

    public function testgetDayShortName(): void
    {
        $date = new DateTimeImmutable('2022-08-08');
        $day = $this->viewController->getDay($date);

        $this->assertEquals('Mo.', $day->getDayShortName());
    }

    public function testgetDayLongName(): void
    {
        $date = new DateTimeImmutable('2022-08-08');
        $day = $this->viewController->getDay($date);

        $this->assertEquals('Montag', $day->getDayName());
    }

    public function testgetDayNumber(): void
    {
        $date = new DateTimeImmutable('2022-08-08');
        $day = $this->viewController->getDay($date);

        $this->assertEquals('08. Aug', $day->getDayNumber());
    }

    public function testHolidayName(): void
    {
        $date = new DateTimeImmutable('2023-05-18');
        $this->holidayRepository
            ->expects($this->once())
            ->method('oneByDate')
            ->willReturn('Himmelfahrt');
        $day = $this->viewController->getDay($date);

        $this->assertEquals('Himmelfahrt', $day->getDayName());
    }

    #[DataProvider('isWeekendProvider')]
    public function testisWeekend(DateTimeImmutable $date, bool $expectedResult): void
    {
        $day = $this->viewController->getDay($date);

        $this->assertEquals($expectedResult, $day->isWeekend());
    }

    public static function isWeekendProvider(): array
    {
        return [
            [new DateTimeImmutable('2022-08-05'), false], // Friday
            [new DateTimeImmutable('2022-08-06'), true], // Saturday
            [new DateTimeImmutable('2022-08-07'), true], // Sunday
            [new DateTimeImmutable('2022-08-08'), false], // Monday
        ];
    }

    public function testVacation(): void
    {
        $date = new DateTimeImmutable('2022-08-08');
        $this->vacationRepository
            ->expects($this->once())
            ->method('byYear')
            ->with('2022')
            ->willReturn([
                new Vacation(new DateTimeImmutable('2022-08-01'), new DateTimeImmutable('2022-08-10'), 'Herbstferien'),
            ]);
        $day = $this->viewController->getDay($date);

        $this->assertTrue($day->isVacation());
        $this->assertEquals('Herbstferien', $day->getVacationName());
    }

    public function testNoVacation(): void
    {
        $date = new DateTimeImmutable('2022-08-08');
        $this->vacationRepository
            ->expects($this->once())
            ->method('byYear')
            ->with('2022')
            ->willReturn([
                new Vacation(new DateTimeImmutable('2022-08-01'), new DateTimeImmutable('2022-08-07'), 'Herbstferien'),
                new Vacation(new DateTimeImmutable('2022-08-09'), new DateTimeImmutable('2022-08-31'), 'Winterferien'),
            ]);
        $day = $this->viewController->getDay($date);

        $this->assertFalse($day->isVacation());
        $this->assertNull($day->getVacationName());
    }
}
