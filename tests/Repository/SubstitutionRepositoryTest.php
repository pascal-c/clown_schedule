<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\substitution;
use App\Repository\SubstitutionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SubstitutionRepositoryTest extends KernelTestCase
{
    public function testfind()
    {
        self::bootKernel();
        $container = static::getContainer();

        # prepare database
        $date = new \DateTimeImmutable('2020-04-18');
        $expectedSubstitution = new Substitution;
        $expectedSubstitution->setDate($date)->setDaytime('am');
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($expectedSubstitution);
        $entityManager->flush();

        $substitutionRepository = $container->get(SubstitutionRepository::class);
        
        $result = $substitutionRepository->find($date, 'pm');
        $this->assertNull($result);

        $result2 = $substitutionRepository->find($date, 'am');
        $this->assertEquals($expectedSubstitution, $result2);
    }
}
