<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\MonthRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MonthRepositoryTest extends KernelTestCase
{
    public function testFindWithoutId()
    {
        $repository = new MonthRepository();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('get')
            ->with('month_id', 'now')
            ->willReturn('2022-07');
        $session
            ->expects($this->never())
            ->method('set');

        $month = $repository->find($session, null);
        $this->assertSame('2022-07', $month->getKey());
    }

    public function testFindWithId()
    {
        $repository = new MonthRepository();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->never())
            ->method('get');
        $session
            ->expects($this->once())
            ->method('set')
            ->with('month_id', '2022-07')
        ;

        $month = $repository->find($session, '2022-07');
        $this->assertSame('2022-07', $month->getKey());
    }
}
