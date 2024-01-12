<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Schedule;
use App\Value\ScheduleStatus;
use PHPUnit\Framework\TestCase;

final class ScheduleTest extends TestCase
{
    public function testStatus(): void
    {
        $schedule = new Schedule();
        $schedule->setStatus(ScheduleStatus::IN_PROGRESS);
        $this->assertSame(ScheduleStatus::IN_PROGRESS, $schedule->getStatus());
    }
}
