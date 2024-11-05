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

    public function testApplyResult(): void
    {
        $playDate1 = (new PlayDate())->setId(1)->setTitle('1');
        $playDate2 = (new PlayDate())->setId(2)->setTitle('2');
        $playDate3 = (new PlayDate())->setId(3)->setTitle('3');
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'));
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'))->setCalculatedPlaysMonth(2);

        $result = Result::create(Month::build('2099-08'))
            ->add($playDate1, $fernando)
            ->add($playDate2, $thorsten)
            ->add($playDate3, null)
        ;

        $this->resultApplier->applyResult($result);

        // Fernando has been assigned to play date 1
        $this->assertSame($playDate1->getPlayingClowns()->first(), $fernando->getClown());
        $this->assertSame(1, $fernando->getCalculatedPlaysMonth()); // has been incremented

        // Thorsten has been assigend to play date 2
        $this->assertSame($playDate2->getPlayingClowns()->first(), $thorsten->getClown());
        $this->assertSame(3, $thorsten->getCalculatedPlaysMonth()); // has been incremented

        // nobody has been assigned to play date 3
        $this->assertEmpty($playDate3->getPlayingClowns());
    }

    public function testApplyAssignemnt(): void
    {
        $playDate = (new PlayDate())->setId(1)->setTitle('1');
        $clownAvailability = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'));

        $this->resultApplier->applyAssignment($playDate, $clownAvailability);

        // Fernando has been assigned to play date 1
        $this->assertSame($playDate->getPlayingClowns()->first(), $clownAvailability->getClown());
        $this->assertSame(1, $clownAvailability->getCalculatedPlaysMonth()); // has been incremented
    }
}
