<?php

declare(strict_types=1);

namespace App\Tests\Lib;

use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Guard\PlayDateGuard;
use App\Repository\ScheduleRepository;
use App\Service\AuthService;
use App\Service\TimeService;
use App\Value\ScheduleStatus;
use Codeception\Stub;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class PlayDateGuardTest extends TestCase
{
    private ScheduleRepository&MockObject $scheduleRepository;
    private TimeService&MockObject $timeService;
    private AuthService&MockObject $authService;
    private PlayDateGuard $playDateGuard;

    public function setUp(): void
    {
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->authService = $this->createMock(AuthService::class);
        $this->playDateGuard = new PlayDateGuard($this->scheduleRepository, $this->timeService, $this->authService);
        parent::setUp();
    }

    #[DataProvider('canDeleteProvider')]
    public function testCanDelete(bool $isAdmin, bool $expected): void
    {
        $this->scheduleRepository->expects($this->never())->method($this->anything());
        $this->timeService->expects($this->never())->method($this->anything());
        $this->authService->method('isAdmin')->willReturn($isAdmin);
        $playDate = Stub::make(PlayDate::class, [
            'date' => new DateTimeImmutable('2027-10-07'),
        ]);

        $this->assertSame($expected, $this->playDateGuard->canDelete($playDate));
    }

    public static function canDeleteProvider(): Generator
    {
        yield 'when is admin' => [
            'isAdmin' => true,
            'expected' => true,
        ];
        yield 'when is not admin' => [
            'isAdmin' => false,
            'expected' => false,
        ];
    }

    #[DataProvider('canCancelProvider')]
    public function testCanCancel(string $today, string $status, bool $isAdmin, bool $expected): void
    {
        $this->timeService->method('today')->willReturn(new DateTimeImmutable($today));
        $this->authService->method('isAdmin')->willReturn($isAdmin);
        $playDate = new PlayDate();
        $playDate->setStatus($status);

        $playDate->setDate(new DateTimeImmutable('2027-10-31'));
        $this->assertSame($expected, $this->playDateGuard->canCancel($playDate));
    }

    public static function canCancelProvider(): Generator
    {
        yield 'when previous month is reached' => [
            'today' => '2027-09-01',
            'status' => PlayDate::STATUS_CONFIRMED,
            'isAdmin' => true,
            'expected' => true,
        ];
        yield 'when previous month is NOT reached' => [
            'today' => '2027-08-31',
            'status' => PlayDate::STATUS_CONFIRMED,
            'isAdmin' => true,
            'expected' => false,
        ];
        yield 'when status is not confirmed' => [
            'today' => '2027-09-01',
            'status' => PlayDate::STATUS_MOVED,
            'isAdmin' => true,
            'expected' => false,
        ];
        yield 'when is not admin' => [
            'today' => '2027-09-01',
            'status' => PlayDate::STATUS_CONFIRMED,
            'isAdmin' => false,
            'expected' => false,
        ];
    }

    #[DataProvider('shouldNotDeleteProvider')]
    public function testShouldNotDelete(?Schedule $schedule, string $today, bool $expected): void
    {
        $this->scheduleRepository->method('find')->willReturn($schedule);
        $this->timeService->method('today')->willReturn(new DateTimeImmutable($today));
        $playDate = Stub::make(PlayDate::class, [
            'date' => new DateTimeImmutable('2027-10-07'),
        ]);
        $this->authService->expects($this->never())->method($this->anything());

        $this->assertSame($expected, $this->playDateGuard->shouldNotDelete($playDate));
    }

    public static function shouldNotDeleteProvider(): Generator
    {
        yield 'when schedule is not completed and date not reached' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::IN_PROGRESS),
            'today' => '2027-10-06',
            'expected' => false,
        ];
        yield 'when schedule is null and date not reached' => [
            'schedule' => null,
            'today' => '2027-10-06',
            'expected' => false,
        ];
        yield 'when schedule is completed' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::COMPLETED),
            'today' => '2027-10-06',
            'expected' => true,
        ];
        yield 'when date is reached' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::IN_PROGRESS),
            'today' => '2027-10-07',
            'expected' => true,
        ];
    }

    #[DataProvider('shouldNotEditProvider')]
    public function testShouldNotEdit(?Schedule $schedule, string $today, bool $isNew, bool $expected): void
    {
        $this->scheduleRepository->method('find')->willReturn($schedule);
        $this->timeService->method('today')->willReturn(new DateTimeImmutable($today));
        $playDate = Stub::make(PlayDate::class, [
            'date' => new DateTimeImmutable('2027-10-07'),
            'id' => $isNew ? null : 42,
        ]);
        $this->authService->expects($this->never())->method($this->anything());

        $this->assertSame($expected, $this->playDateGuard->shouldNotEdit($playDate));
    }

    public static function shouldNotEditProvider(): Generator
    {
        yield 'when schedule is not completed and date not reached' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::IN_PROGRESS),
            'today' => '2027-10-06',
            'isNew' => false,
            'expected' => false,
        ];
        yield 'when schedule is null and date not reached' => [
            'schedule' => null,
            'today' => '2027-10-06',
            'isNew' => false,
            'expected' => false,
        ];
        yield 'when schedule is completed' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::COMPLETED),
            'today' => '2027-10-06',
            'isNew' => false,
            'expected' => true,
        ];
        yield 'when date is reached' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::IN_PROGRESS),
            'today' => '2027-10-07',
            'isNew' => false,
            'expected' => true,
        ];
        yield 'when is new' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::COMPLETED),
            'today' => '2027-10-07',
            'isNew' => true,
            'expected' => false,
        ];
    }
}
