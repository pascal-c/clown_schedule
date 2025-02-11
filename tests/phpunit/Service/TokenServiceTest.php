<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Token;
use App\Factory\ClownFactory;
use App\Service\TimeService;
use App\Service\TokenService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class TokenServiceTest extends KernelTestCase
{
    private TokenService $tokenService;
    private TimeService&MockObject $timeService;
    private EntityManagerInterface $entityManager;
    private ClownFactory $clownFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->clownFactory = $container->get(ClownFactory::class);
        $this->timeService = $this->createMock(TimeService::class);
        $container->set(TimeService::class, $this->timeService);
        $this->tokenService = $container->get(TokenService::class);
        $this->entityManager = $container->get('doctrine.orm.default_entity_manager');
    }

    public function testDeleteExpired()
    {
        $expiresAt = new DateTimeImmutable('2024-02-13 15:00:00');
        $token1 = (new Token())->setToken('abcd')->setExpiresAt($expiresAt->modify('-1 second'))->setClown($this->clownFactory->create());
        $token2 = (new Token())->setToken('dcba')->setExpiresAt($expiresAt->modify('+1 second'))->setClown($this->clownFactory->create());
        $this->entityManager->persist($token1);
        $this->entityManager->persist($token2);
        $this->entityManager->flush();

        $this->timeService
            ->expects($this->once())
            ->method('now')
            ->willReturn($expiresAt);

        $this->tokenService->deleteExpired();
        $allTokens = $this->entityManager->getRepository(Token::class)->findAll();
        $this->assertCount(1, $allTokens);
        $this->assertSame($token2, $allTokens[0]);
    }
}
