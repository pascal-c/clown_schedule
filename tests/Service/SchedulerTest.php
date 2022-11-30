<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Month;
use App\Service\Scheduler;
use PHPUnit\Framework\TestCase;

final class SchedulerTest extends TestCase
{
    public function testcalculate(): void
    {
        $calculator = new Scheduler();
        $month = new Month(new \DateTimeImmutable('1978-12-24'));
        $this->assertSame('1978-12', $month->getKey());
    }
}
