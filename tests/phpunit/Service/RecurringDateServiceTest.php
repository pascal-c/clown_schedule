<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PlayDate;
use App\Entity\RecurringDate;
use App\Entity\Venue;
use App\Factory\PlayDateFactory;
use App\Factory\RecurringDateFactory;
use App\Service\RecurringDateService;
use App\Value\PlayDateType;
use Codeception\Stub;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

final class RecurringDateServiceTest extends KernelTestCase
{
    private Container $container;
    private RecurringDateService $recurringDateService;
    private RecurringDate $recurringDate;

    public function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();

        $this->recurringDateService = $this->container->get(RecurringDateService::class);
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

    public function testDeletePlayDatesSince(): void
    {
        $playDateFactory = $this->container->get(PlayDateFactory::class);
        $recurringDateFactory = $this->container->get(RecurringDateFactory::class);
        $entityManager = $this->container->get(EntityManagerInterface::class);

        $playDateBefore = $playDateFactory->create(date: new DateTimeImmutable('2036-01-25'));
        $playDateSame = $playDateFactory->create(date: new DateTimeImmutable('2036-01-26'));
        $playDateAfter = $playDateFactory->create(date: new DateTimeImmutable('2036-01-27'));
        $_otherPlayDate = $playDateFactory->create(date: new DateTimeImmutable('2036-01-27'));
        $recurringDate = $recurringDateFactory->create(
            endDate: new DateTimeImmutable('2036-01-31'),
            playDates: [$playDateBefore, $playDateSame, $playDateAfter],
        );

        $deletedEntries = $this->recurringDateService->deletePlayDatesSince($recurringDate, new DateTimeImmutable('2036-01-26'));
        $this->assertSame(2, $deletedEntries); // otherPlay date should not be deleted
        $this->assertCount(1, $recurringDate->getPlayDates());
        $this->assertSame($playDateBefore, $recurringDate->getPlayDates()->first());
        $this->assertEquals(new DateTimeImmutable('2036-01-25'), $recurringDate->getEndDate());
        $this->assertTrue($entityManager->contains($recurringDate));

        // when all play dates are deleted, the recurring date should be removed
        $deletedEntries = $this->recurringDateService->deletePlayDatesSince($recurringDate, new DateTimeImmutable('2036-01-25'));
        $this->assertSame(1, $deletedEntries);
        $this->assertCount(0, $recurringDate->getPlayDates());
        $this->assertFalse($entityManager->contains($recurringDate));
    }
}
