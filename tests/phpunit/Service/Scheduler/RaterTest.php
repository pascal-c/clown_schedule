<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Service\Scheduler\Rater;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RaterTest extends TestCase
{
    private Rater $rater;
    private ClownAvailabilityRepository&MockObject $clownAvailabilityRepository;
    private PlayDateRepository&MockObject $playDateRepository;
    private ConfigRepository&MockObject $configRepository;
    private Month $month;
    private array $playDates;
    private array $clownAvailabilities;

    public function setUp(): void
    {
        $this->clownAvailabilityRepository = $this->createMock(ClownAvailabilityRepository::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->rater = new Rater($this->playDateRepository, $this->clownAvailabilityRepository, $this->configRepository);
        $this->month = Month::build('2024-10');

        $this->clownAvailabilities = [
            $clown1 = $this->buildClownAvailability(1, maxPlaysWeek: 1),
            $clown2 = $this->buildClownAvailability(2, availability: 'maybe'),
            $clown3 = $this->buildClownAvailability(3),
        ];
        $this->playDates = [
            $this->buildPlayDate(1, '2024-10-02', [$clown1, $clown2]), // KW 40
            $this->buildPlayDate(2, '2024-10-03', [$clown1, $clown2]), // KW 40
            $this->buildPlayDate(3, '2024-10-04', [$clown2, $clown3]), // KW 40
            $this->buildPlayDate(4, '2024-10-10', [$clown1, $clown2]), // KW 41
            $this->buildPlayDate(5, '2024-10-11', [$clown2]), // KW 41
            $this->buildPlayDate(6, '2024-10-12', []),    // KW 41
        ];
    }

    #[DataProvider('dataProvider')]
    public function testTotalPoints(
        bool $isFeatureMaxPerWeekActive,
        bool $ignoreTargetPlays,
        int $expectedTotalPoints,
        int $expectedPointsNotAssigned,
        int $expectedPointsMaybeClown,
        int $expectedPointsTargetPlays,
        int $expectedPointsMaxPerWeek,
    ): void {
        $this->playDateRepository->expects($this->once())->method('regularByMonth')->with($this->month)->willReturn($this->playDates);
        $this->clownAvailabilityRepository->expects($this->once())->method('byMonth')->with($this->month)->willReturn($this->clownAvailabilities);
        $this->configRepository->method('isFeatureMaxPerWeekActive')->willReturn($isFeatureMaxPerWeekActive);

        $totalPoints = $this->rater->totalPoints($this->month, $ignoreTargetPlays);
        $this->assertSame($expectedTotalPoints, $totalPoints);
    }

    #[DataProvider('dataProvider')]
    public function testPointsPerCategory(
        bool $isFeatureMaxPerWeekActive,
        bool $ignoreTargetPlays,
        int $expectedTotalPoints,
        int $expectedPointsNotAssigned,
        int $expectedPointsMaybeClown,
        int $expectedPointsTargetPlays,
        int $expectedPointsMaxPerWeek,
    ): void {
        $this->playDateRepository->expects($this->once())->method('regularByMonth')->with($this->month)->willReturn($this->playDates);
        $this->clownAvailabilityRepository->expects($this->once())->method('byMonth')->with($this->month)->willReturn($this->clownAvailabilities);
        $this->configRepository->method('isFeatureMaxPerWeekActive')->willReturn($isFeatureMaxPerWeekActive);

        $points = $this->rater->pointsPerCategory($this->month, $ignoreTargetPlays);
        $this->assertSame($expectedPointsNotAssigned, $points['notAssigned']);
        $this->assertSame($expectedPointsMaybeClown, $points['maybeClown']);
        $this->assertSame($expectedPointsTargetPlays, $points['targetPlays']);
        $this->assertSame($expectedPointsMaxPerWeek, $points['maxPerWeek']);
        $this->assertSame($expectedTotalPoints, $points['total']);
    }

    public static function dataProvider(): Generator
    {
        // POINTS_FOR_MISSING_CLOWNS = 300 (3 missing clowns)
        // POINTS_FOR_MAYBE_CLOWNS   = 5   (clown2 has 5 play dates, but is only maybe available)
        // RATE_TARGET_PLAYS = 8   (clown 2 has 5 play dates and clown 3 has 1 play dates, but their target is 3, so 4*2 = 8)
        // RATE_TARGET_PLAYS = 4   (when ignored: only clown 2 has too much plays, so 2*2 = 4)
        // RATE_MAX_PER_WEEK = 10  (clown1 has 2 play dates in KW40, but their maxPlayWeek is 1)
        yield 'has feature MaxPerWeek and do not ignore targetPlays' => [
            'isFeatureMaxPerWeekActive' => true,
            'ignoreTargetPlays' => false,
            'expectedTotalPoints' => 323,
            'expectedPointsNotAssigned' => 300,
            'expectedPointsMaybeClown' => 5,
            'expectedPointsTargetPlays' => 8,
            'expectedPointsMaxPerWeek' => 10,
        ];
        yield 'has feature MaxPerWeek and ignore targetPlays' => [
            'isFeatureMaxPerWeekActive' => true,
            'ignoreTargetPlays' => true,
            'expectedTotalPoints' => 319,
            'expectedPointsNotAssigned' => 300,
            'expectedPointsMaybeClown' => 5,
            'expectedPointsTargetPlays' => 4,
            'expectedPointsMaxPerWeek' => 10,
        ];
        yield 'has NOT feature MaxPerWeek and ignore targetPlays' => [
            'isFeatureMaxPerWeekActive' => false,
            'ignoreTargetPlays' => true,
            'expectedTotalPoints' => 309,
            'expectedPointsNotAssigned' => 300,
            'expectedPointsMaybeClown' => 5,
            'expectedPointsTargetPlays' => 4,
            'expectedPointsMaxPerWeek' => 0,
        ];
        yield 'has NOT feature MaxPerWeek and do not ignore targetPlays' => [
            'isFeatureMaxPerWeekActive' => false,
            'ignoreTargetPlays' => false,
            'expectedTotalPoints' => 313,
            'expectedPointsNotAssigned' => 300,
            'expectedPointsMaybeClown' => 5,
            'expectedPointsTargetPlays' => 8,
            'expectedPointsMaxPerWeek' => 0,
        ];
    }

    private function buildClownAvailability(int $clownId, string $availability = 'yes', ?int $maxPlaysWeek = null): ClownAvailability
    {
        $clown = (new Clown())->setId($clownId);

        return (new ClownAvailability())
            ->setClown($clown)
            ->setMonth($this->month)
            ->setTargetPlays(3)
            ->setSoftMaxPlaysWeek($maxPlaysWeek)
            ->addClownAvailabilityTime($this->buildClownAvailabilityTime('2024-10-02', $availability))
            ->addClownAvailabilityTime($this->buildClownAvailabilityTime('2024-10-03', $availability))
            ->addClownAvailabilityTime($this->buildClownAvailabilityTime('2024-10-04', $availability))
            ->addClownAvailabilityTime($this->buildClownAvailabilityTime('2024-10-10', $availability))
            ->addClownAvailabilityTime($this->buildClownAvailabilityTime('2024-10-11', $availability))
        ;
    }

    private function buildClownAvailabilityTime(string $date, string $availability): ClownAvailabilityTime
    {
        return (new ClownAvailabilityTime())
            ->setDate(new DateTimeImmutable($date))
            ->setAvailability($availability)
            ->setDaytime('am')
        ;
    }

    private function buildPlayDate(int $id, string $date, array $clownAvailabilities = []): PlayDate
    {
        $playDate = (new PlayDate())
            ->setId($id)
            ->setDate(new DateTimeImmutable($date))
            ->setDaytime('am')
        ;
        foreach ($clownAvailabilities as $clownAvailability) {
            $playDate->addPlayingClown($clownAvailability->getClown());
            $clownAvailability->setCalculatedPlaysMonth($clownAvailability->getCalculatedPlaysMonth() + 1);
        }

        return $playDate;
    }
}
