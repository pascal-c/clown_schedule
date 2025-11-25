<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
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
            ->setPlayDateToGiveOff($playDateToGiveOff);

        $this->assertFalse($playDateChangeRequest->isValid());

        $playDateToGiveOff->addPlayingClown($requestedBy);
        $this->assertTrue($playDateChangeRequest->isValid());

        $playDateToGiveOff->cancel();
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
