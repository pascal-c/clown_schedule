<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\Week;
use App\Factory\ClownFactory;
use App\Factory\PlayDateFactory;
use App\Repository\PlayDateRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;

final class PlayDateRepositoryTest extends KernelTestCase
{
    private PlayDateRepository $repository;
    private PlayDateFactory $playDateFactory;
    private ClownFactory $clownFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->playDateFactory = $container->get(PlayDateFactory::class);
        $this->clownFactory = $container->get(ClownFactory::class);
        $this->repository = $container->get(PlayDateRepository::class);
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
}
