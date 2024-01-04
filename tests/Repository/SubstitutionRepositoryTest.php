<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Substitution;
use App\Repository\SubstitutionRepository;
use App\Value\TimeSlotPeriod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SubstitutionRepositoryTest extends KernelTestCase
{
    private SubstitutionRepository $substitutionRepository;
    private \DateTimeInterface $date;
    private Substitution $expectedSubstitution;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $this->substitutionRepository = $container->get(SubstitutionRepository::class);

        $this->date = new \DateTimeImmutable('2020-04-18');
        $this->expectedSubstitution = new Substitution();
        $this->expectedSubstitution->setDate($this->date)->setDaytime('am');
        $entityManager->persist($this->expectedSubstitution);
        $entityManager->flush();
    }

    public function testFind(): void
    {
        $result = $this->substitutionRepository->find($this->date, 'pm');
        $this->assertNull($result);

        $result2 = $this->substitutionRepository->find($this->date, 'am');
        $this->assertEquals($this->expectedSubstitution, $result2);
    }

    public function testFindByTimeSlotPeriod(): void
    {
        $result = $this->substitutionRepository->findByTimeSlotPeriod(new TimeSlotPeriod($this->date, 'pm'));
        $this->assertEmpty($result);

        $result2 = $this->substitutionRepository->findByTimeSlotPeriod(new TimeSlotPeriod($this->date, 'all'));
        $this->assertEquals([$this->expectedSubstitution], $result2);
    }
}
