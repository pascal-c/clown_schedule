<?php

declare(strict_types=1);

namespace App\Tests\Lib;

use App\Entity\Clown;
use App\Entity\Config;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Entity\Venue;
use App\Guard\PlayDateGuard;
use App\Repository\ConfigRepository;
use App\Repository\ScheduleRepository;
use App\Service\AuthService;
use App\Service\TimeService;
use App\Service\VenueService;
use App\Value\ScheduleStatus;
use Codeception\Stub;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
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
    private ConfigRepository&MockObject $configRepository;
    private VenueService&MockObject $venueService;
    private PlayDateGuard $playDateGuard;

    public function setUp(): void
    {
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->authService = $this->createMock(AuthService::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->venueService = $this->createMock(VenueService::class);
        $this->playDateGuard = new PlayDateGuard($this->scheduleRepository, $this->timeService, $this->authService, $this->configRepository, $this->venueService);
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

    #[DataProvider('canAssignProvider')]
    public function testCanAssign(bool $isAdmin, bool $configCanAssign, bool $teamContainsCurrentClown, bool $expected): void
    {
        $playDate = Stub::make(PlayDate::class, [
            'date' => new DateTimeImmutable('2027-10-07'),
            'venue' => $venue = new Venue(),
        ]);

        $this->scheduleRepository->expects($this->never())->method($this->anything());
        $this->timeService->expects($this->never())->method($this->anything());
        $this->authService->method('isAdmin')->willReturn($isAdmin);
        $this->authService->method('getCurrentClown')->willReturn($currentClown = new Clown());
        $this->configRepository->method('find')->willReturn(Stub::make(Config::class, [
            'teamCanAssignPlayingClowns' => $configCanAssign,
        ]));
        $team = $this->createMock(Collection::class);
        $team->method('contains')->with($currentClown)->willReturn($teamContainsCurrentClown);
        $this->venueService->method('getTeam')->with($venue)->willReturn($team);

        $this->assertSame($expected, $this->playDateGuard->canAssign($playDate));
    }

    public static function canAssignProvider(): Generator
    {
        yield 'when is admin' => [
            'isAdmin' => true,
            'configCanAssign' => false,
            'teamContainsCurrentClown' => false,
            'expected' => true,
        ];

        yield 'when is not admin and config does not allow assigning for team' => [
            'isAdmin' => false,
            'configCanAssign' => false,
            'teamContainsCurrentClown' => true,
            'expected' => false,
        ];

        yield 'when is not admin and is not in team' => [
            'isAdmin' => false,
            'configCanAssign' => true,
            'teamContainsCurrentClown' => false,
            'expected' => false,
        ];

        yield 'when is not admin but config allows assigning for team and is in team' => [
            'isAdmin' => false,
            'configCanAssign' => true,
            'teamContainsCurrentClown' => true,
            'expected' => true,
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
