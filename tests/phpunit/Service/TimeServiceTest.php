<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Month;
use App\Service\TimeService;
use Codeception\Stub;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TimeServiceTest extends TestCase
{
    private TimeService $timeService;
    private string $now = '2037-02-17 18:00:00';

    public function setUp(): void
    {
        $this->timeService = Stub::make(TimeService::class, ['now' => new DateTimeImmutable($this->now)]);
    }

    public function testToday(): void
    {
        $this->assertEquals(new DateTimeImmutable('2037-02-17'), $this->timeService->today());
    }

    public function testFirstOfMonth(): void
    {
        $this->assertEquals(new DateTimeImmutable('2037-02-01'), $this->timeService->firstOfMonth());
    }

    public function testNearlyEndOfMonth(): void
    {
        $this->assertEquals(new DateTimeImmutable('2037-02-26'), $this->timeService->NearlyEndOfMonth());
    }

    public function testFirstOfNextMonth(): void
    {
        $this->assertEquals(new DateTimeImmutable('2037-03-01'), $this->timeService->firstOfNextMonth());
    }

    public function testEndOfYear(): void
    {
        $this->assertEquals(new DateTimeImmutable('2037-12-31'), $this->timeService->endOfYear());
    }

    public function testNthWeekdayOfMonth(): void
    {
        $month = new Month(new DateTimeImmutable('2025-08-27'));

        // 2n Tuesday of August 2025
        $date = $this->timeService->nThWeekdayOfMonth(2, 'Tuesday', $month);
        $this->assertEquals(new DateTimeImmutable('2025-08-12'), $date);

        // 1st Friday of August 2025
        $date = $this->timeService->nThWeekdayOfMonth(1, 'Friday', $month);
        $this->assertEquals(new DateTimeImmutable('2025-08-01'), $date);

        // 5th Sunday of August 2025
        $date = $this->timeService->nThWeekdayOfMonth(5, 'Sunday', $month);
        $this->assertEquals(new DateTimeImmutable('2025-08-31'), $date);

        // 5th Monday of August 2025
        $date = $this->timeService->nThWeekdayOfMonth(5, 'Monday', $month);
        $this->assertNull($date, 'There is no 5th Monday in August 2025');
    }
}
