<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Value\TimeSlotPeriod;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateTimeInterface;

final class ClownAvailabilityTest extends TestCase
{
    public function testgetOpenTargetPlays(): void
    {
        $availability = new ClownAvailability();
        $availability->setTargetPlays(3);
        $availability->setCalculatedPlaysMonth(1);

        $this->assertEquals(2, $availability->getOpenTargetPlays());
    }

    public function testGetAvailabilityRatio(): void
    {
        $availability = new ClownAvailability();
        $this->assertNull($availability->getAvailabilityRatio());

        $availability->setAvailabilityRatio(0.5);
        $this->assertEquals(0.5, $availability->getAvailabilityRatio());
    }

    public function testisAvailableOn(): void
    {
        $availability = new ClownAvailability();
        $date1 = new DateTimeImmutable('2022-04-01');
        $date2 = new DateTimeImmutable('2022-04-02');
        $availability->addClownAvailabilityTime($this->buildTimeSlot('yes', $date1, 'am'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('no', $date1, 'pm'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('yes', $date2, 'am'));
        $availability->addClownAvailabilityTime($this->buildTimeSlot('maybe', $date2, 'pm'));

        $this->assertTrue($availability->isAvailableOn(new TimeSlotPeriod($date1, 'am')));
        $this->assertFalse($availability->isAvailableOn(new TimeSlotPeriod($date1, 'pm')));
        $this->assertTrue($availability->isAvailableOn(new TimeSlotPeriod($date2, 'am')));
        $this->assertTrue($availability->isAvailableOn(new TimeSlotPeriod($date2, 'pm')));

        // all day
        $this->assertFalse($availability->isAvailableOn(new TimeSlotPeriod($date1, 'all')));
        $this->assertTrue($availability->isAvailableOn(new TimeSlotPeriod($date2, 'all')));
    }

    public function testgetSoftMaxPlaysAndSubstitutionsWeek(): void
    {
        $availability = new ClownAvailability();
        $this->assertNull($availability->getSoftMaxPlaysAndSubstitutionsWeek());

        $availability->setSoftMaxPlaysWeek(3);
        $this->assertSame(5, $availability->getSoftMaxPlaysAndSubstitutionsWeek());
    }

    private function buildTimeSlot(string $availability, ?DateTimeInterface $date = null, ?string $daytime = 'am'): ClownAvailabilityTime
    {
        $timeSlot = new ClownAvailabilityTime();
        $timeSlot->setAvailability($availability);
        $timeSlot->setDate($date ?? new DateTimeImmutable());
        $timeSlot->setDaytime($daytime);

        return $timeSlot;
    }
}
