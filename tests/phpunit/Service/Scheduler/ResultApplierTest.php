<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultApplier;
use PHPUnit\Framework\TestCase;

final class ResultApplierTest extends TestCase
{
    private ResultApplier $resultApplier;

    public function setUp(): void
    {
        $this->resultApplier = new ResultApplier();
    }

    public function test(): void
    {
        $playDate1 = (new PlayDate())->setId(1)->setTitle('1');
        $playDate2 = (new PlayDate())->setId(2)->setTitle('2');
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'));
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'))->setCalculatedPlaysMonth(2);

        $result = Result::create(Month::build('2099-08'))->add($playDate1, $fernando)->add($playDate2, $thorsten);

        ($this->resultApplier)($result);

        // Fernando has been assigned to play date 1
        $this->assertSame($playDate1->getPlayingClowns()->first(), $fernando->getClown());
        $this->assertSame(1, $fernando->getCalculatedPlaysMonth()); // has been incremented

        // Thorsten has been assigend to play date 2
        $this->assertSame($playDate2->getPlayingClowns()->first(), $thorsten->getClown());
        $this->assertSame(3, $thorsten->getCalculatedPlaysMonth()); // has been incremented
    }
}
