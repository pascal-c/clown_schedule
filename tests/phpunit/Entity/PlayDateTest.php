<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Entity\Fee;
use App\Value\PlayDateType;
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

    public function testType(): void
    {
        $playDate = new PlayDate();
        $this->assertSame(PlayDateType::REGULAR, $playDate->getType());
        $this->assertTrue($playDate->isRegular());

        $playDate->setType(PlayDateType::TRAINING);
        $this->assertSame(PlayDateType::TRAINING, $playDate->getType());
        $this->assertTrue($playDate->isTraining());

        $playDate->setType(PlayDateType::SPECIAL);
        $this->assertSame(PlayDateType::SPECIAL, $playDate->getType());
        $this->assertTrue($playDate->isSpecial());
    }

    public function testGetFee(): void
    {
        $date = new DateTimeImmutable('2024-11-06');
        $playDate = (new PlayDate())->setDate($date);
        $this->assertNull($playDate->getFee());

        $venue = (new Venue())->addFee((new Fee())->setValidFrom($date->modify('+1 day')));
        $playDate->setVenue($venue);
        $this->assertNull($playDate->getFee());

        $venue->addFee($venueFee = (new Fee())->setValidFrom($date->modify('-1 day')));
        $this->assertSame($venueFee, $playDate->getFee());

        $playDate->setFee($playDateFee = new Fee());
        $this->assertSame($playDateFee, $playDate->getFee());
    }
}
