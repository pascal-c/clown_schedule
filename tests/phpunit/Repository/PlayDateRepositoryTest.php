<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Week;
use App\Factory\ClownFactory;
use App\Factory\PlayDateFactory;
use App\Repository\PlayDateRepository;
use App\Service\TimeService;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriod;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;

final class PlayDateRepositoryTest extends KernelTestCase
{
    private PlayDateRepository $repository;
    private PlayDateFactory $playDateFactory;
    private ClownFactory $clownFactory;
    private TimeService|MockObject $timeService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->timeService = $this->createMock(TimeService::class);
        $container->set(TimeService::class, $this->timeService);
        $this->repository = $container->get(PlayDateRepository::class);

        $this->playDateFactory = $container->get(PlayDateFactory::class);
        $this->clownFactory = $container->get(ClownFactory::class);
    }

    public function testCountByClownAvailabilityAndWeek(): void
    {
        $date = new DateTimeImmutable('2024-02-13'); // this is a tuesday
        $week = new Week($date);
        $clown = $this->clownFactory->create();
        $wrongClown = $this->clownFactory->create();

        // we have 2 Plays for this clown for this week
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-11'), playingClowns: [$clown]); // wrong week (this is Sunday before)
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), playingClowns: [$wrongClown]); // wrong clown
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), playingClowns: [$clown]); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-18'), playingClowns: [$clown]); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-19'), playingClowns: [$clown]); // wrong week (this is next Monday)

        $clownAvailability = (new ClownAvailability())
            ->setMonth(new Month($date))
            ->setClown($clown);

        $result = $this->repository->countByClownAvailabilityAndWeek($clownAvailability, $week);
        $this->assertSame(2, $result);
    }

    public function testMinMaxYear(): void
    {
        // it returns the current year if there is no playDate
        $this->timeService->method('currentYear')->willReturn('2001');
        $this->assertSame('2001', $this->repository->minYear());
        $this->assertSame('2001', $this->repository->maxYear());

        // it returns the minimum/maximum otherwise
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-11'));
        $this->playDateFactory->create(date: new DateTimeImmutable('2026-11-11'));
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-06-11'));

        $this->assertSame('1999', $this->repository->minYear());
        $this->assertSame('2026', $this->repository->maxYear());
    }

    public function testByYear(): void
    {
        $year = '1984';

        $this->playDateFactory->create(date: new DateTimeImmutable('1983-12-31')); // wrong year
        $one = $this->playDateFactory->create(date: new DateTimeImmutable('1984-01-01')); // correct!
        $two = $this->playDateFactory->create(date: new DateTimeImmutable('1984-12-31')); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('1985-01-01')); // wrong year

        $result = $this->repository->byYear($year);
        $this->assertEquals([$one, $two], $result);
    }

    public function testByMonth(): void
    {
        $month = Month::build('2024-02');

        $this->playDateFactory->create(date: new DateTimeImmutable('2024-01-31')); // wrong month
        $one = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-01')); // correct!
        $two = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-29')); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-03-01')); // wrong month

        $result = $this->repository->byMonth($month);
        $this->assertEqualsCanonicalizing([$one, $two], $result);
    }

    public function testRegularByMonth(): void
    {
        $month = Month::build('2024-02');

        $this->playDateFactory->create(date: new DateTimeImmutable('2024-01-31')); // wrong month
        $one = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-01')); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-01'), type: PlayDateType::SPECIAL); // wrong type!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-29'), type: PlayDateType::TRAINING); // wrong type!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-03-01')); // wrong month
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-01'), status: PlayDate::STATUS_MOVED); // wrong status

        $result = $this->repository->confirmedRegularByMonth($month);
        $this->assertSame([$one], $result);
    }

    public function testFindByTimeSlotPeriodWithDaytimeAM(): void
    {
        $timeSlotPeriod = new TimeSlotPeriod(
            date: new DateTimeImmutable('2024-02-12'),
            daytime: TimeSlotPeriodInterface::AM,
        );

        $one = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::AM, type: PlayDateType::SPECIAL); // correct!
        $two = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::ALL); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::PM); // wrong daytime!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-13'), daytime: TimeSlotPeriodInterface::AM); // wrong date!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::AM, type: PlayDateType::TRAINING); // wrong type!

        $result = $this->repository->findConfirmedByTimeSlotPeriod($timeSlotPeriod);
        $this->assertEqualsCanonicalizing([$one, $two], $result);
    }

    public function testFindByTimeSlotPeriodWithDaytimeALL(): void
    {
        $timeSlotPeriod = new TimeSlotPeriod(
            date: new DateTimeImmutable('2024-02-12'),
            daytime: TimeSlotPeriodInterface::ALL,
        );

        $one = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::AM); // correct!
        $two = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::ALL); // correct!
        $three = $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-12'), daytime: TimeSlotPeriodInterface::PM); // correct!
        $this->playDateFactory->create(date: new DateTimeImmutable('2024-02-13'), daytime: TimeSlotPeriodInterface::AM); // wrong date!

        $result = $this->repository->findConfirmedByTimeSlotPeriod($timeSlotPeriod);
        $this->assertEqualsCanonicalizing([$one, $two, $three], $result);
    }
}
