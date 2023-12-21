<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Mailer\PlayDateSwapRequestMailer;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Value\PlayDateChangeRequestStatus;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PlayDateChangeRequestCloseInvalidServiceTest extends TestCase
{
    private PlayDateChangeRequestCloseInvalidService $closeInvalidService;
    private PlayDateSwapRequestMailer|MockObject $mailer;

    public function setUp(): void
    {
        $this->mailer = $this->createMock(PlayDateSwapRequestMailer::class);
        $this->closeInvalidService = new PlayDateChangeRequestCloseInvalidService($this->mailer);
    }

    /** @dataProvider closeInvalidDataProvider */
    public function testCloseIfInvalid(bool $isWaiting, bool $isValid, bool $isSwap = true): void
    {
        $playDateChangeRequest = $this->createMock(PlayDateChangeRequest::class);
        $playDateChangeRequest->method('isWaiting')->willReturn($isWaiting);
        $playDateChangeRequest->method('isValid')->willReturn($isValid);
        $playDateChangeRequest->method('isSwap')->willReturn($isSwap);

        if ($isWaiting && !$isValid) {
            $playDateChangeRequest->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        } else {
            $playDateChangeRequest->expects($this->never())->method('setStatus');
        }

        if ($isWaiting && !$isValid && $isSwap) {
            $this->mailer->expects($this->once())->method('sendSwapRequestClosedMail')->with($playDateChangeRequest);
        } else {
            $this->mailer->expects($this->never())->method($this->anything());
        }

        $this->closeInvalidService->closeIfInvalid($playDateChangeRequest);
    }

    public function closeInvalidDataProvider(): Generator
    {
        yield 'waiting and valid' => ['isWaiting' => true, 'isValid' => true];
        yield 'not waiting and valid' => ['isWaiting' => false, 'isValid' => true];
        yield 'not waiting and not valid' => ['isWaiting' => false, 'isValid' => false];
        yield 'waiting and not valid' => ['isWaiting' => true, 'isValid' => false];
        yield 'waiting and not valid and not swap' => ['isWaiting' => true, 'isValid' => false, 'isSwap' => false];
    }

    public function testCloseInvalidChangeRequests(): void
    {
        $playDate = new PlayDate;

        // invalid
        $playDateSwapRequest = $this->createMock(PlayDateChangeRequest::class);
        $playDateSwapRequest->method('isWaiting')->willReturn(true);
        $playDateSwapRequest->method('isValid')->willReturn(false);

        // valid!
        $playDateGiveOffRequest1 = $this->createMock(PlayDateChangeRequest::class);
        $playDateGiveOffRequest1->method('isWaiting')->willReturn(true);
        $playDateGiveOffRequest1->method('isValid')->willReturn(true);

        // invalid
        $playDateGiveOffRequest2 = $this->createMock(PlayDateChangeRequest::class);
        $playDateGiveOffRequest2->method('isWaiting')->willReturn(true);
        $playDateGiveOffRequest2->method('isValid')->willReturn(false);

        $playDate->addPlayDateSwapRequest($playDateSwapRequest);
        $playDate->addPlayDateGiveOffRequest($playDateGiveOffRequest1);
        $playDate->addPlayDateGiveOffRequest($playDateGiveOffRequest2);
        
        $playDateSwapRequest->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        $playDateGiveOffRequest2->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        $playDateGiveOffRequest1->expects($this->never())->method('setStatus');

        $this->closeInvalidService->closeInvalidChangeRequests($playDate);
    }
}
