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

    public function testUnapplyResult(): void
    {
        $playDate1 = (new PlayDate())->setId(1)->setTitle('1');
        $playDate2 = (new PlayDate())->setId(2)->setTitle('2');
        $playDate3 = (new PlayDate())->setId(3)->setTitle('3');
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'))->setCalculatedPlaysMonth(1);
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'))->setCalculatedPlaysMonth(3);
        $playDate1->addPlayingClown($fernando->getClown());
        $playDate2->addPlayingClown($thorsten->getClown());

        $result = Result::create(Month::build('2099-08'))
            ->add($playDate1, $fernando)
            ->add($playDate2, $thorsten)
            ->add($playDate3, null)
        ;

        $this->resultUnapplier->unapplyResult($result);

        // Fernando has been unassigned from play date 1
        $this->assertEmpty($playDate1->getPlayingClowns());
        $this->assertSame(0, $fernando->getCalculatedPlaysMonth()); // has been decremented

        // Thorsten has been unassigend from play date 2
        $this->assertEmpty($playDate2->getPlayingClowns());
        $this->assertSame(2, $thorsten->getCalculatedPlaysMonth()); // has been decremented

        // nothing changed for play date 3
        $this->assertEmpty($playDate3->getPlayingClowns());
    }

    public function testUnapplyAssignment(): void
    {
        $playDate = (new PlayDate())->setId(1)->setTitle('1');
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'))->setCalculatedPlaysMonth(3);
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'))->setCalculatedPlaysMonth(1);
        $playDate
            ->addPlayingClown($thorsten->getClown())
            ->addPlayingClown($fernando->getClown())
        ;

        $this->resultUnapplier->unapplyAssignment($playDate, $thorsten);

        // Thorsten has been unassigend from play date
        $this->assertSame(1, $playDate->getPlayingClowns()->count());
        $this->assertSame($fernando->getClown(), $playDate->getPlayingClowns()->first());
        $this->assertSame(2, $thorsten->getCalculatedPlaysMonth()); // has been decremented
    }
}
