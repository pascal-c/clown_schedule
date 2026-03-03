<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Value\PlayDateChangeRequestType;
use PHPUnit\Framework\TestCase;

final class PlayDateChangeRequestTest extends TestCase
{
    public function testIsValidGiveOff(): void
    {
        $requestedBy = new Clown();
        $playDateToGiveOff = (new PlayDate())
            ->addPlayingClown(new Clown());
        $playDateChangeRequest = (new PlayDateChangeRequest())
            ->setRequestedBy($requestedBy)
            ->setType(PlayDateChangeRequestType::GIVE_OFF)
            ->setPlayDateToGiveOff($playDateToGiveOff);

        // requestedBy does not match
        $this->assertFalse($playDateChangeRequest->isValid());

        // everything's fine
        $playDateToGiveOff->addPlayingClown($requestedBy);
        $this->assertTrue($playDateChangeRequest->isValid());

        // playDateToGiveOff is not confirmed
        $playDateToGiveOff->cancel();
        $this->assertFalse($playDateChangeRequest->isValid());
    }

    public function testIsValidTakeOver(): void
    {
        $requestedBy = new Clown();
        $requestedTo = new Clown();
        $playDateToGiveOff = (new PlayDate())
            ->addPlayingClown(new Clown());
        $playDateChangeRequest = (new PlayDateChangeRequest())
            ->setRequestedBy($requestedBy)
            ->setType(PlayDateChangeRequestType::TAKE_OVER)
            ->setPlayDateToGiveOff($playDateToGiveOff)
            ->setRequestedTo($requestedTo);

        // everything's fine
        $this->assertTrue($playDateChangeRequest->isValid());

        // playDateToGiveOff is not confirmed
        $playDateToGiveOff->cancel();
        $this->assertFalse($playDateChangeRequest->isValid());

        // there are already enough clowns
        $playDateToGiveOff->setStatus(PlayDate::STATUS_CONFIRMED);
        $playDateToGiveOff->setNeededClowns(1);
        $this->assertFalse($playDateChangeRequest->isValid());
    }

    public function testIsValidSwap(): void
    {
        $requestedBy = new Clown();
        $requestedTo = new Clown();
        $playDateToGiveOff = (new PlayDate())
            ->addPlayingClown(new Clown());
        $playDateWanted = (new PlayDate())
        ->addPlayingClown(new Clown());
        $playDateChangeRequest = (new PlayDateChangeRequest())
            ->setRequestedBy($requestedBy)
            ->setType(PlayDateChangeRequestType::SWAP)
            ->setPlayDateToGiveOff($playDateToGiveOff)
            ->setRequestedTo($requestedTo)
            ->setPlayDateWanted($playDateWanted);

        // requestedBy does not match
        $this->assertFalse($playDateChangeRequest->isValid());

        // reqeustedTo does not match
        $playDateToGiveOff->addPlayingClown($requestedBy);
        $this->assertFalse($playDateChangeRequest->isValid());

        // everything's fine
        $playDateWanted->addPlayingClown($requestedTo);
        $this->assertTrue($playDateChangeRequest->isValid());

        // playDateWanted is cancelled
        $playDateWanted->cancel();
        $this->assertFalse($playDateChangeRequest->isValid());
    }
}
