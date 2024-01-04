<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateHistory;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateHistoryService;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PlayDateHistoryServiceTest extends TestCase
{
    private TimeService|MockObject $timeService;
    private PlayDateChangeRequestCloseInvalidService|MockObject $closeInvalidService;
    private PlayDateHistoryService $playDateHistoryService;

    public function setUp(): void
    {
        $this->timeService = $this->createMock(TimeService::class);
        $this->closeInvalidService = $this->createMock(PlayDateChangeRequestCloseInvalidService::class);
        $this->playDateHistoryService = new PlayDateHistoryService(
            $this->timeService,
            $this->closeInvalidService
        );
    }

    public function testAdd(): void
    {
        $playDate = new PlayDate();
        $playingClown = new Clown();
        $playDate->addPlayingClown($playingClown);
        $changingClown = new Clown();

        $now = new \DateTimeImmutable();
        $this->timeService->expects($this->once())->method('now')->willReturn($now);
        $this->closeInvalidService->expects($this->once())->method('closeInvalidChangeRequests');

        $this->playDateHistoryService->add($playDate, $changingClown, PlayDateChangeReason::MANUAL_CHANGE);
        $this->assertNotEmpty($playDate->getPlayDateHistory());
        /** @var PlayDateHistory $playDateHistoryEntry */
        $playDateHistoryEntry = $playDate->getPlayDateHistory()->first();

        $this->assertSame($now, $playDateHistoryEntry->getChangedAt());
        $this->assertSame($changingClown, $playDateHistoryEntry->getChangedBy());
        $this->assertSame(PlayDateChangeReason::MANUAL_CHANGE, $playDateHistoryEntry->getReason());
        $this->assertCount(1, $playDateHistoryEntry->getPlayingClowns());
        $this->assertSame($playingClown, $playDateHistoryEntry->getPlayingClowns()->first());
    }
}
