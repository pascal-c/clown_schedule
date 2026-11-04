<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Factory\ScheduleFactory;
use App\Repository\ScheduleRepository;
use App\Service\TimeService;
use App\Value\ScheduleStatus;
use Codeception\Stub;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ScheduleRepositoryTest extends KernelTestCase
{
    private ScheduleRepository $repository;
    private Schedule $expectedSchedule;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $timeService = Stub::make(TimeService::class, [
            'now' => fn () => new DateTimeImmutable('2025-10-14'),
        ]);
        $container->set(TimeService::class, $timeService);

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $this->repository = $container->get(ScheduleRepository::class);
        $factory = new ScheduleFactory($entityManager);

        $this->expectedSchedule = $factory->create(Month::build('2022-11'));
        $entityManager->persist($this->expectedSchedule);
        $entityManager->persist($factory->create(Month::build('2022-10')));
        $entityManager->persist($factory->create(Month::build('2022-12')));
        $entityManager->flush();
    }

    public function testFindPersistedSchedule()
    {
        $result = $this->repository->find(Month::build('2022-11'));
        $this->assertEquals($this->expectedSchedule, $result);
    }

    public function testFindPastNonPersistedSchedule()
    {
        $schedule = $this->repository->find(Month::build('2025-09'));

        $this->assertNull($schedule->getId());
        $this->assertEquals(ScheduleStatus::COMPLETED, $schedule->getStatus());
        $this->assertSame('2025-09', $schedule->getMonth()->getKey());
    }

    public function testFindFutureNonPersistedSchedule()
    {
        $schedule = $this->repository->find(Month::build('2025-10'));

        $this->assertNull($schedule->getId());
        $this->assertEquals(ScheduleStatus::NOT_STARTED, $schedule->getStatus());
        $this->assertSame('2025-10', $schedule->getMonth()->getKey());
    }
}
