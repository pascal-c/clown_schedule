<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Service\PlayDateHistoryService;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\TrainingAssigner;
use App\Value\PlayDateChangeReason;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TrainingAssignerTest extends TestCase
{
    private AvailabilityChecker&MockObject $availabilityChecker;
    private PlayDateHistoryService&MockObject $playDateHistoryService;
    private TrainingAssigner $trainingAssigner;

    protected function setUp(): void
    {
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $this->trainingAssigner = new TrainingAssigner($this->availabilityChecker, $this->playDateHistoryService);
    }

    public function testAssignAllAvailableWithAvailableClowns(): void
    {
        $clown1 = $this->createMock(Clown::class);
        $clown2 = $this->createMock(Clown::class);

        $clownAvailability1 = $this->createMock(ClownAvailability::class);
        $clownAvailability1->method('getClown')->willReturn($clown1);

        $clownAvailability2 = $this->createMock(ClownAvailability::class);
        $clownAvailability2->method('getClown')->willReturn($clown2);

        $training1 = $this->createMock(PlayDate::class);
        $training2 = $this->createMock(PlayDate::class);

        $this->availabilityChecker
            ->expects($this->exactly(4))
            ->method('isAvailableOn')
            ->willReturnMap([
                [$training1, $clownAvailability1, true],
                [$training1, $clownAvailability2, false],
                [$training2, $clownAvailability1, false],
                [$training2, $clownAvailability2, true],
            ]);

        $training1->expects($this->once())->method('addPlayingClown')->with($clown1);
        $training2->expects($this->once())->method('addPlayingClown')->with($clown2);

        $this->trainingAssigner->assignAllAvailable(
            [$clownAvailability1, $clownAvailability2],
            [$training1, $training2]
        );
    }

    public function testAssignAllAvailableWithNoAvailableClowns(): void
    {
        $clownAvailability = $this->createMock(ClownAvailability::class);
        $training = $this->createMock(PlayDate::class);

        $this->availabilityChecker
            ->expects($this->once())
            ->method('isAvailableOn')
            ->with($training, $clownAvailability)
            ->willReturn(false);

        $training->expects($this->never())->method('addPlayingClown');

        $this->trainingAssigner->assignAllAvailable([$clownAvailability], [$training]);
    }

    public function testAssignOne(): void
    {
        $clown = $this->createMock(Clown::class);
        $training = $this->createMock(PlayDate::class);

        $training->expects($this->once())->method('addPlayingClown')->with($clown);
        $this->playDateHistoryService->expects($this->once())->method('add')->with($training, $clown, PlayDateChangeReason::MANUAL_CHANGE);

        $this->trainingAssigner->assignOne($training, $clown);
    }

    public function testUnassignOne(): void
    {
        $clown = $this->createMock(Clown::class);
        $training = $this->createMock(PlayDate::class);

        $training->expects($this->once())->method('removePlayingClown')->with($clown);
        $this->playDateHistoryService->expects($this->once())->method('add')->with($training, $clown, PlayDateChangeReason::MANUAL_CHANGE);

        $this->trainingAssigner->unassignOne($training, $clown);
    }
}
