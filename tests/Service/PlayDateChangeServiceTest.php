<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Entity\PlayDateHistory;
use App\Service\PlayDateChangeService;
use App\Service\PlayDateHistoryService;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use App\Value\PlayDateChangeRequestStatus;
use App\Value\PlayDateChangeRequestType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PlayDateChangeServiceTest extends TestCase
{
    private PlayDateChangeService $playDateChangeService;
    private PlayDateHistoryService|MockObject $playDateHistoryService;

    public function setUp(): void
    {
        $this->playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $this->playDateChangeService = new PlayDateChangeService(
            $this->playDateHistoryService,
        );
    }

    public function testAccept_giveOff(): void
    {
        $requestedBy = new Clown;
        $requestedTo = new Clown;
        $playingClown1 = new Clown;
        $playingClown2 = $requestedBy;
        $playDateToGiveOff = (new PlayDate)
            ->addPlayingClown($playingClown1)
            ->addPlayingClown($playingClown2);
        $playDateChangeRequest = (new PlayDateChangeRequest)
            ->setPlayDateToGiveOff($playDateToGiveOff)
            ->setPlayDateWanted(null)
            ->setRequestedBy($requestedBy)
            ->setRequestedTo(null)
            ->setType(PlayDateChangeRequestType::GIVE_OFF)
            ->setStatus(PlayDateChangeRequestStatus::WAITING);
           
        $this->playDateHistoryService->expects($this->once())
            ->method('add')
            ->with($playDateToGiveOff, $requestedBy, PlayDateChangeReason::GIVE_OFF);

        $this->playDateChangeService->accept($playDateChangeRequest, $requestedTo);
        
        // first playing clown did not change
        $this->assertContains($playingClown1, $playDateToGiveOff->getPlayingClowns());
        $this->assertNotContains($requestedBy, $playDateToGiveOff->getPlayingClowns());
        $this->assertContains($requestedTo, $playDateToGiveOff->getPlayingClowns());

        $this->assertSame($requestedTo, $playDateChangeRequest->getRequestedTo());
    }

    public function testAccept_swap(): void
    {
        $requestedBy = new Clown;
        $requestedTo = new Clown;
        $playingClown1 = new Clown;
        $playDateToGiveOff = (new PlayDate)
            ->addPlayingClown($playingClown1)
            ->addPlayingClown($requestedBy);
        $playDateWanted = (new PlayDate)
            ->addPlayingClown($playingClown1)
            ->addPlayingClown($requestedTo);    
        $playDateChangeRequest = (new PlayDateChangeRequest)
            ->setPlayDateToGiveOff($playDateToGiveOff)
            ->setPlayDateWanted($playDateWanted)
            ->setRequestedBy($requestedBy)
            ->setRequestedTo($requestedTo)
            ->setType(PlayDateChangeRequestType::SWAP)
            ->setStatus(PlayDateChangeRequestStatus::WAITING);
           
        $this->playDateHistoryService->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$playDateToGiveOff, $requestedBy, PlayDateChangeReason::SWAP],
                [$playDateWanted, $requestedBy, PlayDateChangeReason::SWAP],
            );

        $this->playDateChangeService->accept($playDateChangeRequest, $requestedTo);
        
        $this->assertTrue($playDateChangeRequest->isAccepted());
        // first playing clown did not change
        $this->assertContains($playingClown1, $playDateToGiveOff->getPlayingClowns());
        $this->assertNotContains($requestedBy, $playDateToGiveOff->getPlayingClowns());
        $this->assertContains($requestedTo, $playDateToGiveOff->getPlayingClowns());

        // first playing clown did not change
        $this->assertContains($playingClown1, $playDateWanted->getPlayingClowns());
        $this->assertNotContains($requestedTo, $playDateWanted->getPlayingClowns());
        $this->assertContains($requestedBy, $playDateWanted->getPlayingClowns());
    }
}
