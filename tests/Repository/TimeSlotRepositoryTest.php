<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\TimeSlot;
use App\Repository\TimeSlotRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TimeSlotRepositoryTest extends KernelTestCase
{
    public function testfind()
    {
        self::bootKernel();
        $container = static::getContainer();

        # prepare database
        $date = new \DateTimeImmutable('2020-04-18');
        $expectedTimeSlot = new TimeSlot;
        $expectedTimeSlot->setDate($date)->setDaytime('am');
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($expectedTimeSlot);
        $entityManager->flush();

        $timeSlotRepository = $container->get(TimeSlotRepository::class);
        
        $result = $timeSlotRepository->find($date, 'pm');
        $this->assertNull($result);

        $result2 = $timeSlotRepository->find($date, 'am');
        $this->assertEquals($expectedTimeSlot, $result2);
    }
}
