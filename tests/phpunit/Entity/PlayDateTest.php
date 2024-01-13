<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PlayDate;
use App\Entity\Venue;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PlayDateTest extends TestCase
{
    public function testSetDaytime(): void
    {
        $playDate = new PlayDate();
        $playDate->setDaytime('pm');
        $this->assertSame('pm', $playDate->getDaytime());
    }

    public function testGetMeetingTime(): void
    {
        $playDate = (new PlayDate());
        $this->assertNull($playDate->getMeetingTime());

        $venue = (new Venue())->setMeetingTime(new DateTimeImmutable('12:45'));
        $playDate->setVenue($venue);
        $this->assertEquals(new DateTimeImmutable('12:45'), $playDate->getMeetingTime());

        $playDate->setMeetingTime(new DateTimeImmutable('15:12'));
        $this->assertEquals(new DateTimeImmutable('15:12'), $playDate->getMeetingTime());
    }

    public function testPlayTimeFrom(): void
    {
        $playDate = (new PlayDate());
        $this->assertNull($playDate->getPlayTimeFrom());

        $venue = (new Venue())->setPlayTimeFrom(new DateTimeImmutable('12:45'));
        $playDate->setVenue($venue);
        $this->assertEquals(new DateTimeImmutable('12:45'), $playDate->getPlayTimeFrom());

        $playDate->setPlayTimeFrom(new DateTimeImmutable('15:12'));
        $this->assertEquals(new DateTimeImmutable('15:12'), $playDate->getPlayTimeFrom());
    }

    public function testPlayTimeTo(): void
    {
        $playDate = (new PlayDate());
        $this->assertNull($playDate->getPlayTimeTo());

        $venue = (new Venue())->setPlayTimeTo(new DateTimeImmutable('12:45'));
        $playDate->setVenue($venue);
        $this->assertEquals(new DateTimeImmutable('12:45'), $playDate->getPlayTimeTo());

        $playDate->setPlayTimeTo(new DateTimeImmutable('15:12'));
        $this->assertEquals(new DateTimeImmutable('15:12'), $playDate->getPlayTimeTo());
    }
}
