<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler\AvailabilityChecker;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Week;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;

final class MaxPlaysReachedTest extends TestCase
{
    private AvailabilityChecker $availabilityChecker;
    private PlayDateRepository|MockObject $playDateRepository;
    private SubstitutionRepository|MockObject $substitutionRepository;
    private ConfigRepository|MockObject $configRepository;

    public function setUp(): void
    {
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->availabilityChecker = new AvailabilityChecker(
            $this->playDateRepository,
            $this->substitutionRepository,
            $this->configRepository,
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMaxPlaysWeekReached(?int $maxPlaysWeek, bool $expectedResult, bool $hasFeatureMaxPerWeek = true): void
    {
        $date = new DateTimeImmutable('2024-02-13'); // this is a tuesday
        $week = new Week($date);
        $month = new Month($date);
        $clown = new Clown();
        $clownAvailability = (new ClownAvailability())
            ->setMonth($month)
            ->setClown($clown)
            ->setSoftMaxPlaysWeek($maxPlaysWeek);
        $otherPlayDates = [ // we have 2 Plays for this clown for this week
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-11'))->addPlayingClown($clown), // wrong week (this is Sunday before)
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-12'))->addPlayingClown(new Clown()), // wrong clown
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-12'))->addPlayingClown($clown), // correct!
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-18'))->addPlayingClown($clown), // correct!
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-19'))->addPlayingClown($clown), // wrong week (this is next Monday)
        ];
        $this->playDateRepository
            ->method('byMonth')
            ->with($month)
            ->willReturn($otherPlayDates);
        $this->configRepository
            ->method('hasFeatureMaxPerWeek')
            ->with()
            ->willReturn($hasFeatureMaxPerWeek);

        $result = $this->availabilityChecker->maxPlaysWeekReached($week, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMaxPlaysAndSubstitutionsWeekReached(?int $maxPlaysWeek, bool $expectedResult, bool $hasFeatureMaxPerWeek = true): void
    {
        $date = new DateTimeImmutable('2024-02-13'); // this is a tuesday
        $week = new Week($date);
        $month = new Month($date);
        $clown = new Clown();
        $clownAvailability = (new ClownAvailability())
            ->setMonth($month)
            ->setClown($clown)
            ->setSoftMaxPlaysWeek($maxPlaysWeek);

        $otherPlayDates = [ // we have 2 Plays for this clown for this week
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-11'))->addPlayingClown($clown), // wrong week (this is Sunday before)
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-12'))->addPlayingClown(new Clown()), // wrong clown
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-12'))->addPlayingClown($clown), // correct!
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-18'))->addPlayingClown($clown), // correct!
            (new PlayDate())->setDate(new DateTimeImmutable('2024-02-19'))->addPlayingClown($clown), // wrong week (this is next Monday)
        ];
        $this->playDateRepository
            ->method('byMonth')
            ->with($month)
            ->willReturn($otherPlayDates);

        $otherSubstitutions = [ // we have 1 substitution for this clown for this week
            (new Substitution())->setDate(new DateTimeImmutable('2024-02-11'))->setSubstitutionClown($clown), // wrong week (this is Sunday before)
            (new Substitution())->setDate(new DateTimeImmutable('2024-02-12'))->setSubstitutionClown(new Clown()), // wrong clown
            (new Substitution())->setDate(new DateTimeImmutable('2024-02-12'))->setSubstitutionClown(null), // no clown is also wrong clown
            (new Substitution())->setDate(new DateTimeImmutable('2024-02-18'))->setSubstitutionClown($clown), // correct!
            (new Substitution())->setDate(new DateTimeImmutable('2024-02-19'))->setSubstitutionClown($clown), // wrong week (this is next Monday)
        ];
        $this->substitutionRepository
            ->method('byMonth')
            ->with($month)
            ->willReturn($otherSubstitutions);
        $this->configRepository
            ->method('hasFeatureMaxPerWeek')
            ->with()
            ->willReturn($hasFeatureMaxPerWeek);

        $result = $this->availabilityChecker->maxPlaysAndSubstitutionsWeekReached($week, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    public function dataProvider(): Generator
    {
        yield 'when clown has no max given' => [
            'maxPlaysWeek' => null, // => maxPlaysAndSubstitutionsWeek == null
            'expectedResult' => false,
        ];
        yield 'when clown has 3 maxPlaysWeek' => [
            'maxPlaysWeek' => 3, // => maxPlaysAndSubstitutionsWeek == 5
            'expectedResult' => false,
        ];
        yield 'when clown has 2 maxPlaysWeek' => [
            'maxPlaysWeek' => 2, // => maxPlaysAndSubstitutionsWeek == 3
            'expectedResult' => true,
        ];
        yield 'when clown has 1 maxPlaysWeek' => [
            'maxPlaysWeek' => 1, // => maxPlaysAndSubstitutionsWeek == 2
            'expectedResult' => true,
        ];
        yield 'when feature maxPlaysWeek is disabled' => [
            'maxPlaysWeek' => 1, // => maxPlaysAndSubstitutionsWeek == 2
            'expectedResult' => false,
            'hasFeatureMaxPerWeek' => false,
        ];
    }
}
