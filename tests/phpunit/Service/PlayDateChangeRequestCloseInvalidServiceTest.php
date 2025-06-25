<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Mailer\PlayDateSwapRequestMailer;
use App\Repository\PlayDateChangeRequestRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\TimeService;
use App\Value\PlayDateChangeRequestStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

final class PlayDateChangeRequestCloseInvalidServiceTest extends TestCase
{
    private PlayDateChangeRequestCloseInvalidService $closeInvalidService;
    private PlayDateSwapRequestMailer&MockObject $mailer;
    private TimeService&MockObject $timeService;
    private PlayDateChangeRequestRepository&MockObject $playDateChangeRequestRepository;

    public function setUp(): void
    {
        $this->mailer = $this->createMock(PlayDateSwapRequestMailer::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->timeService->method('today')->willReturn(new DateTimeImmutable('2024-01-05'));
        $this->playDateChangeRequestRepository = $this->createMock(PlayDateChangeRequestRepository::class);
        $this->closeInvalidService = new PlayDateChangeRequestCloseInvalidService(
            $this->mailer,
            $this->timeService,
            $this->playDateChangeRequestRepository,
        );
    }

    #[DataProvider('closeInvalidDataProvider')]
    #[DataProvider('dataProviderWithWantedDateNotMet')]
    public function testCloseIfInvalidWithSwapRequest(bool $isWaiting, bool $isValid, string $giveOffDate, string $wantedDate, bool $expectClose): void
    {
        $playDateChangeRequest = $this->createMock(PlayDateChangeRequest::class);
        $playDateChangeRequest->method('isWaiting')->willReturn($isWaiting);
        $playDateChangeRequest->method('isValid')->willReturn($isValid);
        $playDateChangeRequest->method('isSwap')->willReturn(true);
        $playDateChangeRequest->method('isGiveOff')->willReturn(false);
        $playDateChangeRequest->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable($giveOffDate)));
        $playDateChangeRequest->method('getPlayDateWanted')->willReturn((new PlayDate())->setDate(new DateTimeImmutable($wantedDate)));

        if ($expectClose) {
            $playDateChangeRequest->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
            $this->mailer->expects($this->once())->method('sendSwapRequestClosedMail')->with($playDateChangeRequest);
        } else {
            $playDateChangeRequest->expects($this->never())->method('setStatus');
            $this->mailer->expects($this->never())->method($this->anything());
        }

        $this->closeInvalidService->closeIfInvalid($playDateChangeRequest);
    }

    #[DataProvider('closeInvalidDataProvider')]
    public function testCloseIfInvalidWithGiveOffRequest(bool $isWaiting, bool $isValid, string $giveOffDate, string $wantedDate, bool $expectClose): void
    {
        $playDateChangeRequest = $this->createMock(PlayDateChangeRequest::class);
        $playDateChangeRequest->method('isWaiting')->willReturn($isWaiting);
        $playDateChangeRequest->method('isValid')->willReturn($isValid);
        $playDateChangeRequest->method('isSwap')->willReturn(false);
        $playDateChangeRequest->method('isGiveOff')->willReturn(true);
        $playDateChangeRequest->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable($giveOffDate)));

        if ($expectClose) {
            $playDateChangeRequest->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        } else {
            $playDateChangeRequest->expects($this->never())->method('setStatus');
        }

        $this->mailer->expects($this->never())->method($this->anything());

        $this->closeInvalidService->closeIfInvalid($playDateChangeRequest);
    }

    public static function closeInvalidDataProvider(): Generator
    {
        yield 'not waiting and valid' => [
            'isWaiting' => false,
            'isValid' => true,
            'giveOffDate' => '2024-01-05',
            'wantedDate' => '2024-01-05',
            'expectClose' => false,
        ];
        yield 'not waiting and not valid' => [
            'isWaiting' => false,
            'isValid' => false,
            'giveOffDate' => '2024-01-05',
            'wantedDate' => '2024-01-05',
            'expectClose' => false,
        ];
        yield 'waiting and valid and deadline met' => [
            'isWaiting' => true,
            'isValid' => true,
            'giveOffDate' => '2024-01-07',
            'wantedDate' => '2024-01-07',
            'expectClose' => false,
        ];
        yield 'waiting and not valid and deadline met' => [
            'isWaiting' => true,
            'isValid' => false,
            'giveOffDate' => '2024-01-07',
            'wantedDate' => '2024-01-07',
            'expectClose' => true,
        ];
        yield 'waiting and valid but deadline for giveOffDate not met' => [
            'isWaiting' => true,
            'isValid' => true,
            'giveOffDate' => '2024-01-06',
            'wantedDate' => '2024-01-07',
            'expectClose' => true,
        ];
    }

    public static function dataProviderWithWantedDateNotMet(): Generator
    {
        yield 'waiting and valid but deadline for wantedDate not met' => [
            'isWaiting' => true,
            'isValid' => true,
            'giveOffDate' => '2024-01-07',
            'wantedDate' => '2024-01-06',
            'expectClose' => true,
        ];
    }

    public function testCloseInvalidChangeRequests(): void
    {
        $playDate = new PlayDate();

        // invalid
        $playDateSwapRequest = $this->createMock(PlayDateChangeRequest::class);
        $playDateSwapRequest->method('isWaiting')->willReturn(true);
        $playDateSwapRequest->method('isValid')->willReturn(false);
        $playDateSwapRequest->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));
        $playDateSwapRequest->method('getPlayDateWanted')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));

        // valid!
        $playDateGiveOffRequest1 = $this->createMock(PlayDateChangeRequest::class);
        $playDateGiveOffRequest1->method('isWaiting')->willReturn(true);
        $playDateGiveOffRequest1->method('isValid')->willReturn(true);
        $playDateGiveOffRequest1->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));
        $playDateGiveOffRequest1->method('getPlayDateWanted')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));

        // invalid
        $playDateGiveOffRequest2 = $this->createMock(PlayDateChangeRequest::class);
        $playDateGiveOffRequest2->method('isWaiting')->willReturn(true);
        $playDateGiveOffRequest2->method('isValid')->willReturn(false);
        $playDateGiveOffRequest2->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));
        $playDateGiveOffRequest2->method('getPlayDateWanted')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));

        $playDate->addPlayDateSwapRequest($playDateSwapRequest);
        $playDate->addPlayDateGiveOffRequest($playDateGiveOffRequest1);
        $playDate->addPlayDateGiveOffRequest($playDateGiveOffRequest2);

        $playDateSwapRequest->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        $playDateGiveOffRequest2->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        $playDateGiveOffRequest1->expects($this->never())->method('setStatus');

        $this->closeInvalidService->closeInvalidChangeRequests($playDate);
    }

    public function testCloseAllInvalidChangeRequests(): void
    {
        // valid!
        $request1 = $this->createMock(PlayDateChangeRequest::class);
        $request1->method('isGiveOff')->willReturn(true);
        $request1->method('isWaiting')->willReturn(true);
        $request1->method('isValid')->willReturn(true);
        $request1->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));

        // invalid
        $request2 = $this->createMock(PlayDateChangeRequest::class);
        $request2->method('isGiveOff')->willReturn(true);
        $request2->method('isWaiting')->willReturn(true);
        $request2->method('isValid')->willReturn(false);
        $request2->method('getPlayDateToGiveOff')->willReturn((new PlayDate())->setDate(new DateTimeImmutable('2024-08-05')));

        $this->playDateChangeRequestRepository
            ->expects($this->once())
            ->method('findAllRequestsWaiting')
            ->willReturn([$request1, $request2]);
        $request2->expects($this->once())->method('setStatus')->with(PlayDateChangeRequestStatus::CLOSED);
        $request1->expects($this->never())->method('setStatus');

        $this->closeInvalidService->closeAllInvalidChangeRequests();
    }
}
