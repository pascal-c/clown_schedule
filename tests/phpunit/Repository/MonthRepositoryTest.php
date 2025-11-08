<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\MonthRepository;
use App\Service\TimeService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MonthRepositoryTest extends KernelTestCase
{
    private MonthRepository $monthRepository;
    private TimeService&MockObject $timeService;

    public function setUp(): void
    {
        $this->timeService = $this->createMock(TimeService::class);
        $this->monthRepository = new MonthRepository($this->timeService);
    }

    public function testFindWithoutIdButWithSession()
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('get')
            ->with('month_id', null)
            ->willReturn('2022-07');
        $session
            ->expects($this->never())
            ->method('set');
        $this->timeService
            ->expects($this->never())
            ->method($this->anything());

        $month = $this->monthRepository->find($session, null);
        $this->assertSame('2022-07', $month->getKey());
    }

    public function testFindWithoutIdAndWithoutSession()
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('get')
            ->with('month_id', null)
            ->willReturn(null);
        $session
            ->expects($this->never())
            ->method('set');
        $this->timeService
            ->expects($this->once())
            ->method('now')
            ->willReturn(new DateTimeImmutable('2022-08-11 22:00:00'));

        $month = $this->monthRepository->find($session, null);
        $this->assertSame('2022-08', $month->getKey());
    }

    public function testFindWithId()
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->never())
            ->method('get');
        $session
            ->expects($this->once())
            ->method('set')
            ->with('month_id', '2022-07');
        $this->timeService
            ->expects($this->never())
            ->method($this->anything());

        $month = $this->monthRepository->find($session, '2022-07');
        $this->assertSame('2022-07', $month->getKey());
    }
}
