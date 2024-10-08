<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultUnapplier;
use PHPUnit\Framework\TestCase;

final class ResultUnapplierTest extends TestCase
{
    private ResultUnapplier $resultUnapplier;

    public function setUp(): void
    {
        $this->resultUnapplier = new ResultUnapplier();
    }

    public function test(): void
    {
        $playDate1 = (new PlayDate())->setId(1)->setTitle('1');
        $playDate2 = (new PlayDate())->setId(2)->setTitle('2');
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'))->setCalculatedPlaysMonth(1);
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'))->setCalculatedPlaysMonth(3);
        $playDate1->addPlayingClown($fernando->getClown());
        $playDate2->addPlayingClown($thorsten->getClown());

        $result = Result::create(Month::build('2099-08'))->add($playDate1, $fernando)->add($playDate2, $thorsten);

        ($this->resultUnapplier)($result);

        // Fernando has been unassigned from play date 1
        $this->assertEmpty($playDate1->getPlayingClowns());
        $this->assertSame(0, $fernando->getCalculatedPlaysMonth()); // has been decremented

        // Thorsten has been unassigend from play date 2
        $this->assertEmpty($playDate2->getPlayingClowns());
        $this->assertSame(2, $thorsten->getCalculatedPlaysMonth()); // has been decremented
    }
}
