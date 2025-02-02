<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\Token;
use App\Repository\ClownRepository;
use App\Repository\TokenRepository;
use App\Service\AuthService;
use App\Service\TimeService;
use App\Service\TokenService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

final class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private ClownRepository&MockObject $clownRepository;
    private RequestStack&MockObject $requestStack;
    private TokenGeneratorInterface&MockObject $tokenGenerator;
    private EntityManagerInterface&MockObject $entityManager;
    private TokenRepository&MockObject $tokenRepository;
    private TimeService&MockObject $timeService;
    private TokenService&MockObject $tokenService;

    public function setUp(): void
    {
        $this->clownRepository = $this->createMock(ClownRepository::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tokenRepository = $this->createMock(TokenRepository::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->tokenService = $this->createMock(TokenService::class);

        $this->authService = new AuthService(
            $this->clownRepository,
            $this->requestStack,
            $this->tokenGenerator,
            $this->entityManager,
            $this->tokenRepository,
            $this->timeService,
            $this->tokenService,
        );
    }

    public function testGetLoginToken(): void
    {
        /** @var Clown&MockObject $clown */
        $clown = $this->createMock(Clown::class);

        $this->tokenService->expects($this->once())
            ->method('deleteExpired');
        $this->tokenGenerator->expects($this->once())
            ->method('generateToken')
            ->willReturn('abcdef');
        $this->timeService->expects($this->once())
            ->method('now')
            ->willReturn(new DateTimeImmutable('2024-10-01 12:34:56'));
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with(self::callback(function (Token $token) use ($clown) {
                $this->assertEquals('abcdef', $token->getToken());
                $this->assertEquals($clown, $token->getClown());
                $this->assertEquals(new DateTimeImmutable('2024-10-01 13:34:56'), $token->getExpiresAt());

                return true;
            }));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $token = $this->authService->getLoginToken($clown);
        $this->assertEquals('abcdef', $token);
    }

    public function testLoginByTokenWithSuccess(): void
    {
        $token = $this->createMock(Token::class);
        $clown = $this->createMock(Clown::class);
        $clown->expects($this->once())
            ->method('getId')
            ->willReturn(42);
        $token->expects($this->exactly(2))
            ->method('getClown')
            ->willReturn($clown);
        $this->tokenRepository->expects($this->once())
            ->method('find')
            ->with('abcdef')
            ->willReturn($token);
        $this->requestStack->expects($this->once())
            ->method('getSession')
            ->willReturn($session = $this->createMock(Session::class));
        $session->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function (string $key, mixed $value) {
                static $calls = [
                    ['isLoggedIn', true],
                    ['currentClownId', 42],
                ];
                $expected = array_shift($calls);
                $this->assertEquals($expected[0], $key);
                $this->assertEquals($expected[1], $value);
            });
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($token);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $success = $this->authService->loginByToken('abcdef');
        $this->assertTrue($success);

        // Test that the currentClown is cached
        $this->assertSame($clown, $this->authService->getCurrentClown());
    }

    public function testLoginByTokenWithWithFailure(): void
    {
        $this->tokenRepository->expects($this->once())
            ->method('find')
            ->with('abcdef')
            ->willReturn(null);
        $this->requestStack->expects($this->once())
            ->method('getSession')
            ->willReturn($session = $this->createMock(Session::class));
        $session->expects($this->never())
            ->method('set');
        $this->entityManager->expects($this->never())
            ->method('remove');
        $this->entityManager->expects($this->never())
            ->method('flush');

        $success = $this->authService->loginByToken('abcdef');
        $this->assertFalse($success);
    }
}
