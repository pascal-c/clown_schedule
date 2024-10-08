<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\Scheduler\Result;
use PHPUnit\Framework\TestCase;

final class ResultTest extends TestCase
{
    public function test(): void
    {
        $playDate1 = (new PlayDate())->setId(1)->setTitle('1');
        $playDate2 = (new PlayDate())->setId(2)->setTitle('2');
        $fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando'));
        $thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten'));

        $result = Result::create(Month::build('now'))->add($playDate1, $thorsten);
        $newResult = $result->add($playDate2, $fernando);

        // the first result stays immutable
        $this->assertSame([$playDate1], $result->getPlayDates());
        $this->assertSame($thorsten, $result->getAssignedClownAvailability($playDate1));

        // the new result contains the added play Date and its assigned clown
        $this->assertSame([$playDate1, $playDate2], $newResult->getPlayDates());
        $this->assertSame($thorsten, $newResult->getAssignedClownAvailability($playDate1));
        $this->assertSame($fernando, $newResult->getAssignedClownAvailability($playDate2));
    }

    public function testGetMonth(): void
    {
        $month = Month::build('2044-09');
        $result = Result::create($month);
        $this->assertSame($month, $result->getMonth());
    }
}
