<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Schedule;
use App\Value\ScheduleStatus;

final class ScheduleTest extends TestCase
{
    public function testStatus(): void
    {
        $schedule = new Schedule;
        $schedule->setStatus(ScheduleStatus::IN_PROGRESS);
        $this->assertSame(ScheduleStatus::IN_PROGRESS, $schedule->getStatus());
    }
}
