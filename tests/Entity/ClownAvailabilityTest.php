<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;

final class ClownAvailabilityTest extends TestCase
{
    public function testgetOpenEntitledPlays(): void
    {
        $availability = new ClownAvailability;
        $availability->setEntitledPlaysMonth(2.3);
        $availability->setCalculatedPlaysMonth(1);

        $this->assertEqualsWithDelta(1.3, $availability->getOpenEntitledPlays(), 0.0000001);
    }

    public function testgetAvailabilityRatio(): void
    {
        $availability = new ClownAvailability;
        $availability->addClownAvailabilityTime($this->buildTimeSlot('yes'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('maybe'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('no'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('no'));

        $this->assertEquals(0.5, $availability->getAvailabilityRatio());
    }

    public function testisAvailableOn(): void
    {
        $availability = new ClownAvailability;
        $date1 = new \DateTimeImmutable('2022-04-01');
        $date2 = new \DateTimeImmutable('2022-04-02');
        $availability->addClownAvailabilityTime($this->buildTimeSlot('yes', $date1, 'am'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('no', $date1, 'pm'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('no', $date2, 'am'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('maybe', $date2, 'pm'));

        $this->assertTrue($availability->isAvailableOn($date1, 'am'));
        $this->assertFalse($availability->isAvailableOn($date1, 'pm'));
        $this->assertFalse($availability->isAvailableOn($date2, 'am'));
        $this->assertTrue($availability->isAvailableOn($date2, 'pm'));
    }

    private function buildTimeSlot(string $availability, ?\DateTimeInterface $date = null, ?string $daytime = 'am'): ClownAvailabilityTime
    {
        $timeSlot = new ClownAvailabilityTime;
        $timeSlot->setAvailability($availability);
        $timeSlot->setDate($date ?? new \DateTimeImmutable);
        $timeSlot->setDaytime($daytime);
        return $timeSlot;
    }
}
