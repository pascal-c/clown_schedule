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
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultRater;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResultRaterTest extends TestCase
{
    private ResultRater $resultRater;
    private ClownAvailabilityRepository|MockObject $clownAvailabilityRepository;
    private ConfigRepository|MockObject $configRepository;

    public function setUp(): void
    {
        $this->clownAvailabilityRepository = $this->createMock(ClownAvailabilityRepository::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->resultRater = new ResultRater($this->clownAvailabilityRepository, $this->configRepository);
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(bool $hasFeatureMaxPerWeek, bool $ignoreTargetPlays, int $expectedRate): void
    {
        $clownAvailabilites = [
            $clown1 = $this->buildClownAvailability(1, maxPlaysWeek: 1),
            $clown2 = $this->buildClownAvailability(2, availability: 'maybe'),
            $clown3 = $this->buildClownAvailability(3),
        ];
        $month = Month::build('2024-10');
        $result = Result::create($month)
            ->add($this->buildPlayDate(1, '2024-10-02'), $clown1) // KW 40
            ->add($this->buildPlayDate(2, '2024-10-03'), $clown1) // KW 40
            ->add($this->buildPlayDate(3, '2024-10-04'), $clown2) // KW 40
            ->add($this->buildPlayDate(4, '2024-10-10'), $clown1) // KW 41
            ->add($this->buildPlayDate(5, '2024-10-11'), $clown2) // KW 41
            ->add($this->buildPlayDate(6, '2024-10-12'), null)    // KW 41
        ;
        $this->clownAvailabilityRepository->expects($this->once())->method('byMonth')->with($month)->willReturn($clownAvailabilites);
        $this->configRepository->method('hasFeatureMaxPerWeek')->willReturn($hasFeatureMaxPerWeek);

        $rate = ($this->resultRater)($result, $ignoreTargetPlays);
        $this->assertSame($expectedRate, $rate);
    }

    public function dataProvider(): Generator
    {
        // RATE_NOT_ASSIGNED = 100 (1 not assigned play date)
        // RATE_MAX_PER_WEEK = 10  (clown1 has 2 play dates in KW40, but their maxPlayWeek is 1)
        // RATE_MAYBE        = 2   (clown2 has 2 play dates, but is only maybe available)
        // RATE_TARGET_PLAYS = 6   (clown 1 has 3 play dates and clown 3 has 0 play dates, but their target is 1, so 3*2 = 6)
        yield 'has feature MaxPerWeek and do not ignore targetPlays' => [
            'hasFeatureMaxPerWeek' => true,
            'ignoreTargetPlays' => false,
            'expectedRate' => 118, // 100 + 10 + 2 + 6
        ];
        yield 'has feature MaxPerWeek and ignore targetPlays' => [
            'hasFeatureMaxPerWeek' => true,
            'ignoreTargetPlays' => true,
            'expectedRate' => 112,  // 100 + 10 + 2
        ];
        yield 'has NOT feature MaxPerWeek and ignore targetPlays' => [
            'hasFeatureMaxPerWeek' => false,
            'ignoreTargetPlays' => true,
            'expectedRate' => 102, // 100 + 2
        ];
        yield 'has NOT feature MaxPerWeek and do not ignore targetPlays' => [
            'hasFeatureMaxPerWeek' => false,
            'ignoreTargetPlays' => false,
            'expectedRate' => 108, // 100 + 2 + 6
        ];
    }

    private function buildClownAvailability(int $clownId, string $availability = 'yes', ?int $maxPlaysWeek = null): ClownAvailability
    {
        $clown = (new Clown())->setId($clownId);

        return (new ClownAvailability())
            ->setClown($clown)
            ->setTargetPlays(2)
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

    private function buildPlayDate(int $id, string $date): PlayDate
    {
        return (new PlayDate())
            ->setId($id)
            ->setDate(new DateTimeImmutable($date))
            ->setDaytime('am')
        ;
    }
}
