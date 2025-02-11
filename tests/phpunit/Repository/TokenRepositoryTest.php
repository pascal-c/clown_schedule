<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Token;
use App\Factory\ClownFactory;
use App\Repository\TokenRepository;
use App\Service\TimeService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;

final class TokenRepositoryTest extends KernelTestCase
{
    private TokenRepository $repository;
    private Token $expectedToken;
    private TimeService|MockObject $timeService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $clownFactory = $container->get(ClownFactory::class);
        $this->timeService = $this->createMock(TimeService::class);
        $container->set(TimeService::class, $this->timeService);
        $this->repository = $container->get(TokenRepository::class);

        $expiresAt = new DateTimeImmutable('2024-02-13 15:00:00');
        $this->expectedToken = (new Token())->setToken('abcd')->setExpiresAt($expiresAt)->setClown($clownFactory->create());
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($this->expectedToken);
        $entityManager->flush();
    }

    public function testFind()
    {
        $this->timeService
            ->expects($this->exactly(2))
            ->method('now')
            ->willReturn(new DateTimeImmutable('2024-02-13 14:59:59'));

        $this->assertNull($this->repository->find('wrong token'));

        $result = $this->repository->find('abcd');
        $this->assertEquals($this->expectedToken, $result);
    }

    public function testFindExpired()
    {
        $this->timeService
            ->expects($this->once())
            ->method('now')
            ->willReturn(new DateTimeImmutable('2024-02-13 15:00:01'));

        $result = $this->repository->find('abcd');
        $this->assertNull($result);
    }
}
