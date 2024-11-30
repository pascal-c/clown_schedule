<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TimeService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TimeServiceTest extends TestCase
{
    private TimeService $timeService;

    public function setUp(): void
    {
        $this->timeService = new TimeService();
    }

    public function testToday(): void
    {
        $this->assertEquals(new DateTimeImmutable('today'), $this->timeService->today());
    }

    public function testFirstOfMonth(): void
    {
        $today = new DateTimeImmutable('today');
        $expected = new DateTimeImmutable($today->format('Y-m').'-01');
        $this->assertEquals($expected, $this->timeService->firstOfMonth());
    }

    public function testNearlyEndOfMonth(): void
    {
        $today = new DateTimeImmutable('today');
        $expected = new DateTimeImmutable($today->format('Y-m').'-26');
        $this->assertEquals($expected, $this->timeService->NearlyEndOfMonth());
    }
}
