<?php

declare(strict_types=1);

namespace App\Tests\Gateway\RosterCalculator;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Gateway\RosterCalculator\RosterResult;
use App\Gateway\RosterCalculator\RosterResultApplier;
use App\Repository\ClownRepository;
use App\Repository\PlayDateRepository;
use App\Service\PlayDateHistoryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RosterResultApplierTest extends TestCase
{
    private PlayDateRepository&MockObject $playDateRepository;
    private ClownRepository&MockObject $clownRepository;
    private PlayDateHistoryService&MockObject $playDateHistoryService;
    private RosterResultApplier $rosterResultApplier;

    protected function setUp(): void
    {
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->clownRepository = $this->createMock(ClownRepository::class);
        $this->playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $this->rosterResultApplier = new RosterResultApplier($this->playDateRepository, $this->clownRepository, $this->playDateHistoryService);
    }

    public function testApply(): void
    {
        $rosterResult = new RosterResult(
            assignments: [
                ['shiftId' => '1', 'personIds' => ['11']],
                ['shiftId' => '2', 'personIds' => ['11', '12']],
            ],
            personalResults: [
                ['personId' => '11', 'calculatedShifts' => 2],
                ['personId' => '12', 'calculatedShifts' => 1],
            ]
        );
        $month = Month::build('2028');
        $playDate1 = (new PlayDate())->setId(1);
        $playDate2 = (new PlayDate())->setId(2);
        $clownAvailability1 = (new ClownAvailability())->setMonth($month);
        $clownAvailability2 = (new ClownAvailability())->setMonth($month);
        $clown1 = (new Clown())->setId(11)->addClownAvailability($clownAvailability1);
        $clown2 = (new Clown())->setId(12)->addClownAvailability($clownAvailability2);

        $this->playDateRepository->expects($this->exactly(2))->method('find')->willReturnMap([[1, $playDate1], [2, $playDate2]]);
        $this->clownRepository->expects($this->exactly(5))->method('find')->willReturnMap([[11, $clown1], [12, $clown2]]);
        $this->playDateHistoryService->expects($this->exactly(2))->method('add');

        $this->rosterResultApplier->apply($rosterResult, $month);

        $this->assertSame([$clown1], $playDate1->getPlayingClowns()->toArray());
        $this->assertSame([$clown1, $clown2], $playDate2->getPlayingClowns()->toArray());
        $this->assertSame(2, $clownAvailability1->getCalculatedPlaysMonth());
        $this->assertSame(1, $clownAvailability2->getCalculatedPlaysMonth());
    }
}
