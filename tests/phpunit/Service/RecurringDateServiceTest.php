<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PlayDate;
use App\Entity\RecurringDate;
use App\Entity\Venue;
use App\Service\RecurringDateService;
use App\Service\TimeService;
use App\Value\PlayDateType;
use Codeception\Stub;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

final class RecurringDateServiceTest extends TestCase
{
    private RecurringDateService $recurringDateService;

    private RecurringDate $recurringDate;

    public function setUp(): void
    {
        $this->recurringDateService = new RecurringDateService(new TimeService());
        $this->recurringDate = Stub::make(RecurringDate::class, [
            'playDates' => new ArrayCollection(),
            'startDate' => new DateTimeImmutable('2025-08-01'),
            'endDate' => new DateTimeImmutable('2025-09-31'),
            'dayOfWeek' => 'Tuesday',
            'every' => 2,

            'daytime' => 'am',
            'meetingTime' => new DateTimeImmutable('08:30'),
            'playTimeFrom' => new DateTimeImmutable('09:00'),
            'playTimeTo' => new DateTimeImmutable('11:00'),
            'venue' => new Venue(),
            'isSuper' => true,
        ]);

    }

    public function testBuildPlayDatesWeekly(): void
    {
        $this->recurringDate->setRhythm(RecurringDate::RHYTHM_WEEKLY);
        $this->recurringDateService->buildPlayDates($this->recurringDate);
        $expectedDates = [
            new DateTimeImmutable('2025-08-05'),
            new DateTimeImmutable('2025-08-19'),
            new DateTimeImmutable('2025-09-02'),
            new DateTimeImmutable('2025-09-16'),
            new DateTimeImmutable('2025-09-30'),
        ];
        foreach ($this->recurringDate->getPlayDates() as $k => $playDate) {
            $this->assertPlayDateMatchesRecurringDate($playDate, $expectedDates[$k]);
        }
    }

    public function testBuildPlayDatesMonthly(): void
    {
        $this->recurringDate->setRhythm(RecurringDate::RHYTHM_MONTHLY);
        $this->recurringDateService->buildPlayDates($this->recurringDate);
        $expectedDates = [
            new DateTimeImmutable('2025-08-12'),
            new DateTimeImmutable('2025-09-09'),
        ];
        foreach ($this->recurringDate->getPlayDates() as $k => $playDate) {
            $this->assertPlayDateMatchesRecurringDate($playDate, $expectedDates[$k]);
        }
    }

    public function testBuildPlayDatesMonthlyWhenFirstDateIsBeforeStartDate(): void
    {
        $this->recurringDate->setRhythm(RecurringDate::RHYTHM_MONTHLY);
        $this->recurringDate->setStartDate(new DateTimeImmutable('2025-08-13'));
        $this->recurringDateService->buildPlayDates($this->recurringDate);
        $expectedDates = [
            new DateTimeImmutable('2025-09-09'),
        ];
        foreach ($this->recurringDate->getPlayDates() as $k => $playDate) {
            $this->assertPlayDateMatchesRecurringDate($playDate, $expectedDates[$k]);
        }
    }

    public function testBuildPlayDatesMonthlyWhenFifthWeekdayIsChosen(): void
    {
        $this->recurringDate->setRhythm(RecurringDate::RHYTHM_MONTHLY);
        $this->recurringDate->setEvery(5);
        $this->recurringDateService->buildPlayDates($this->recurringDate);
        $expectedDates = [
            new DateTimeImmutable('2025-09-30'),
        ];
        foreach ($this->recurringDate->getPlayDates() as $k => $playDate) {
            $this->assertPlayDateMatchesRecurringDate($playDate, $expectedDates[$k]);
        }
    }

    protected function assertPlayDateMatchesRecurringDate(PlayDate $playDate, DateTimeImmutable $date): void
    {
        $this->assertEquals($date, $playDate->getDate());

        $this->assertEquals('am', $playDate->getDaytime());
        $this->assertEquals(new DateTimeImmutable('08:30'), $playDate->getMeetingTime());
        $this->assertEquals(new DateTimeImmutable('09:00'), $playDate->getPlayTimeFrom());
        $this->assertEquals(new DateTimeImmutable('11:00'), $playDate->getPlayTimeTo());
        $this->assertTrue($playDate->isSuper());
        $this->assertSame(PlayDateType::REGULAR, $playDate->getType());
        $this->assertSame($this->recurringDate->getVenue(), $playDate->getVenue());
    }
}
