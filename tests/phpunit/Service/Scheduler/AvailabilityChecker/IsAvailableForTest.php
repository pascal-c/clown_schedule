<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler\AvailabilityChecker;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\AvailabilityChecker\MaxPlaysReachedChecker;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

final class IsAvailableForTest extends TestCase
{
    private AvailabilityChecker $availabilityChecker;
    private PlayDateRepository&MockObject $playDateRepository;
    private SubstitutionRepository&MockObject $substitutionRepository;
    private MaxPlaysReachedChecker&MockObject $maxPlaysReachedChecker;

    public function setUp(): void
    {
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->maxPlaysReachedChecker = $this->createMock(MaxPlaysReachedChecker::class);
        $this->availabilityChecker = new AvailabilityChecker(
            $this->playDateRepository,
            $this->substitutionRepository,
            $this->maxPlaysReachedChecker,
        );
    }

    #[DataProvider('dataProvider')]
    public function testIsAvailableFor(
        ClownAvailability $clownAvailability,
        array $otherPlayDates = [],
        string $firstClownGender = 'male',
        bool $expectedResult = true,
        ?Substitution $substitution = null,
        ?Clown $blockedClown = null,
        bool $maxPlaysMonthReached = false,
        bool $maxPlaysDayReached = false,
    ): void {
        $playDate = self::buildPlayDate('am', (new Clown())->setGender($firstClownGender), $blockedClown ?? new Clown());
        $this->playDateRepository
            ->expects($this->atMost(1))
            ->method('confirmedByMonth')
            ->with($this->equalTo(Month::build('2022-04')))
            ->willReturn($otherPlayDates);
        $this->substitutionRepository
            ->expects($this->atMost(1))
            ->method('find')
            ->willReturn($substitution);
        $this->maxPlaysReachedChecker
            ->expects($this->atMost(1))
            ->method('maxPlaysMonthReached')
            ->with($clownAvailability)
            ->willReturn($maxPlaysMonthReached);
        $this->maxPlaysReachedChecker
            ->expects($this->atMost(1))
            ->method('maxPlaysDayReached')
            ->with($this->equalTo(new DateTimeImmutable('2022-04-01')), $clownAvailability)
            ->willReturn($maxPlaysDayReached);

        $result = $this->availabilityChecker->isAvailableFor($playDate, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    public static function dataProvider(): array
    {
        $clownAvailability = self::buildClownAvailability('yes');
        $playDateOnSameTimeSlot = self::buildPlayDate('am', $clownAvailability->getClown());
        $substitution = self::buildSubstitution()->setSubstitutionClown($clownAvailability->getClown());

        return [
            'when clown is available' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'expectedResult' => true,
            ],
            'when clown is maybe available' => [
                'clownAvailability' => self::buildClownAvailability('maybe'),
                'expectedResult' => true,
            ],
            'when clown is NOT available' => [
                'clownAvailability' => self::buildClownAvailability('no'),
                'expectedResult' => false,
            ],
            'when maxPlaysMonth reached' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'maxPlaysMonthReached' => true,
                'expectedResult' => false,
            ],
            'when maxPlaysDay reached' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'maxPlaysDayReached' => true,
                'expectedResult' => false,
            ],
            'with other play on same timeslot, but not for this clown' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'otherPlayDates' => [self::buildPlayDate()],
                'expectedResult' => true,
            ],
            'when clown is available, but is already substitution clown' => [
                'clownAvailability' => $clownAvailability,
                'substitution' => $substitution,
                'expectedResult' => false,
            ],
            'with other play on same timeslot for this clown' => [
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [$playDateOnSameTimeSlot],
                'expectedResult' => false,
            ],
            'with one male one not' => [
                'clownAvailability' => self::buildClownAvailability('yes', gender: 'male'),
                'firstClownGender' => 'diverse',
                'expectedResult' => true,
            ],
            'with two males' => [
                'clownAvailability' => self::buildClownAvailability('yes', gender: 'male'),
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            'when this clown is blocked' => [
                'clownAvailability' => $clownAvailability,
                'blockedClown' => $clownAvailability->getClown(),
                'expectedResult' => false,
            ],
        ];
    }

    #[DataProvider('dataProviderForSubstitution')]
    public function testIsAvailableForSubstitution(
        ClownAvailability $clownAvailability,
        array $otherPlayDates = [],
        ?Substitution $otherSubstitution = null,
        bool $maxSubstitutionsMonthReached = false,
        bool $maxPlaysDayReached = false,
        bool $expectedResult = true,
    ): void {
        $substitution = self::buildSubstitution();
        $this->playDateRepository
            ->expects($this->atMost(1))
            ->method('confirmedByMonth')
            ->with($this->equalTo(Month::build('2022-04')))
            ->willReturn($otherPlayDates);
        $this->substitutionRepository
            ->expects($this->atMost(1))
            ->method('find')
            ->willReturn($otherSubstitution);
        $this->maxPlaysReachedChecker
            ->expects($this->atMost(1))
            ->method('maxSubstitutionsMonthReached')
            ->with($clownAvailability)
            ->willReturn($maxSubstitutionsMonthReached);
        $this->maxPlaysReachedChecker
            ->expects($this->atMost(1))
            ->method('maxPlaysDayReached')
            ->with($this->equalTo(new DateTimeImmutable('2022-04-01')), $clownAvailability)
            ->willReturn($maxPlaysDayReached);

        $result = $this->availabilityChecker->isAvailableForSubstitution($substitution, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    public static function dataProviderForSubstitution(): array
    {
        $clownAvailability = self::buildClownAvailability('yes');
        $playDateOnSameTimeSlot = self::buildPlayDate('am', $clownAvailability->getClown());
        $substitution = self::buildSubstitution()->setSubstitutionClown($clownAvailability->getClown());

        return [
            'when clown is available' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'expectedResult' => true,
            ],
            'when clown is maybe available' => [
                'clownAvailability' => self::buildClownAvailability('maybe'),
                'expectedResult' => true,
            ],
            'when clown is NOT available' => [
                'clownAvailability' => self::buildClownAvailability('no'),
                'expectedResult' => false,
            ],
            'when maxSubstitutionsMonth reached' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'maxSubstitutionsMonthReached' => true,
                'expectedResult' => false,
            ],
            'when maxPlaysDay reached' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'maxPlaysDayReached' => true,
                'expectedResult' => false,
            ],
            'with other play on same timeslot, but not for this clown' => [
                'clownAvailability' => self::buildClownAvailability('yes'),
                'otherPlayDates' => [self::buildPlayDate()],
                'expectedResult' => true,
            ],
            'when clown is available, but is already substitution clown' => [
                'clownAvailability' => $clownAvailability,
                'otherSubstitution' => $substitution,
                'expectedResult' => false,
            ],
            'with other play on same timeslot for this clown' => [
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [$playDateOnSameTimeSlot],
                'expectedResult' => false,
            ],
        ];
    }

    private static function buildPlayDate(string $daytime = 'am', ?Clown $clown = null, ?Clown $blockedClown = null): PlayDate
    {
        $venue = new Venue();
        if (!is_null($blockedClown)) {
            $venue->addBlockedClown($blockedClown);
        }

        $playDate = new PlayDate();
        $playDate
            ->setDate(new DateTimeImmutable('2022-04-01'))
            ->setDaytime($daytime)
            ->setVenue($venue);
        if (!is_null($clown)) {
            $playDate->addPlayingClown($clown);
        }

        return $playDate;
    }

    private static function buildSubstitution(string $daytime = 'am'): Substitution
    {
        return (new Substitution())
            ->setDate(new DateTimeImmutable('2022-04-01'))
            ->setDaytime($daytime);
    }

    private static function buildClownAvailability(
        string $availability,
        string $gender = 'diverse',
    ): ClownAvailability {
        $clownAvailability = new ClownAvailability();
        $clownAvailability->setClown((new Clown())->setGender($gender));
        $clownAvailability->setMonth(Month::build('2022-04'));
        $date = new DateTimeImmutable('2022-04-01');
        $clownAvailability->addClownAvailabilityTime(self::buildAvailabilityTimeSlot($availability, $date, 'am'));

        return $clownAvailability;
    }

    private static function buildAvailabilityTimeSlot(string $availability, DateTimeInterface $date, string $daytime): ClownAvailabilityTime
    {
        $timeSlot = new ClownAvailabilityTime();
        $timeSlot->setAvailability($availability);
        $timeSlot->setDate($date);
        $timeSlot->setDaytime($daytime);

        return $timeSlot;
    }
}
