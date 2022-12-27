<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Month;
use App\Entity\TimeSlot;

final class TimeSlotTest extends TestCase
{
    public function testsetDate(): void
    {
        $date = new \DateTimeImmutable('2022-11-28');
        $timeSlot = new TimeSlot;
        $timeSlot->setDate($date);
        $this->assertSame('2022-11', $timeSlot->getMonth()->getKey());
    }
}
