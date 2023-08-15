<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Entity\substitution;
use App\Factory\ScheduleFactory;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ScheduleRepositoryTest extends KernelTestCase
{
    private ScheduleRepository $repository;
    private Schedule $expectedSchedule;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $this->repository = $container->get(ScheduleRepository::class);  
        $factory = new ScheduleFactory($entityManager);       

        $this->expectedSchedule = $factory->create(Month::build('2022-11'));
        $entityManager->persist($this->expectedSchedule);
        $entityManager->persist($factory->create(Month::build('2022-10')));
        $entityManager->persist($factory->create(Month::build('2022-12')));
        $entityManager->flush();
    }

    public function testFind()
    {
        $result = $this->repository->find(Month::build('1872-11'));
        $this->assertNull($result);

        $result2 = $this->repository->find(Month::build('2022-11'));
        $this->assertEquals($this->expectedSchedule, $result2);
    }
}
