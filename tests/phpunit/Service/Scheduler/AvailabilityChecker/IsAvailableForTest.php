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
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateTimeInterface;

final class IsAvailableForTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsAvailableFor(
        ClownAvailability $clownAvailability,
        array $otherPlayDates = [],
        string $firstClownGender = 'male',
        bool $expectedResult = true,
        ?Substitution $isSubstitutionClown = null,
        ?Clown $blockedClown = null,
    ): void {
        $playDate = $this->buildPlayDate('am', (new Clown())->setGender($firstClownGender), $blockedClown ?? new Clown());
        $playDateRepository = $this->createMock(PlayDateRepository::class);
        $playDateRepository
            ->method('byMonth')
            ->with($this->equalTo(Month::build('2022-04')))
            ->willReturn($otherPlayDates);
        $substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $substitutionRepository->expects($this->atMost(1))
            ->method('find')
            ->willReturn($isSubstitutionClown);
        $configRepository = $this->createMock(ConfigRepository::class);

        $availabilityChecker = new AvailabilityChecker($playDateRepository, $substitutionRepository, $configRepository);
        $result = $availabilityChecker->isAvailableFor($playDate, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    public function dataProvider(): array
    {
        $clownAvailability = $this->buildClownAvailability('yes');
        $clownAvailabilityWithMaxPlaysDay2 = $this->buildClownAvailability('yes')
            ->setClown($clownAvailability->getClown())
            ->setMaxPlaysDay(2);

        $playDateOnSameTimeSlot = $this->buildPlayDate('am', $clownAvailability->getClown());
        $playDateOnSameDay = $this->buildPlayDate('pm', $clownAvailability->getClown());

        $substitution = $this->buildSubstitution()->setSubstitutionClown($clownAvailability->getClown());

        return [
            [ // clown is available
                'clownAvailability' => $this->buildClownAvailability('yes'),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => true,
            ],
            [ // clown is available
                'clownAvailability' => $this->buildClownAvailability('maybe'),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => true,
            ],
            [ // clown is not available
                'clownAvailability' => $this->buildClownAvailability('no'),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // maxPlays reached
                'clownAvailability' => $this->buildClownAvailability('yes', maxPlaysReached: true),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // other play on same timeslot, but not for this clown
                'clownAvailability' => $this->buildClownAvailability('yes'),
                'otherPlayDates' => [$this->buildPlayDate()],
                'firstClownGender' => 'male',
                'expectedResult' => true,
            ],
            [ // clown is available, but is already substitution clown
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
                'substitution' => $substitution,
            ],
            [ // other play on same timeslot for this clown
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [$playDateOnSameTimeSlot],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // other play on same day for this clown
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [$playDateOnSameDay],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // other play on same day for this clown but higher max
                'clownAvailability' => $clownAvailabilityWithMaxPlaysDay2,
                'otherPlayDates' => [$playDateOnSameDay],
                'firstClownGender' => 'male',
                'expectedResult' => true,
            ],
            [ // one male one not
                'clownAvailability' => $this->buildClownAvailability('yes', gender: 'male'),
                'otherPlayDates' => [],
                'firstClownGender' => 'diverse',
                'expectedResult' => true,
            ],
            [ // two males
                'clownAvailability' => $this->buildClownAvailability('yes', gender: 'male'),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // two males
                'clownAvailability' => $this->buildClownAvailability('yes', gender: 'male'),
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
            ],
            [ // when this clown is blocked
                'clownAvailability' => $clownAvailability,
                'otherPlayDates' => [],
                'firstClownGender' => 'male',
                'expectedResult' => false,
                'substitution' => null,
                'blockedClown' => $clownAvailability->getClown(),
            ],
        ];
    }

    private function buildPlayDate(string $daytime = 'am', ?Clown $clown = null, ?Clown $blockedClown = null): PlayDate
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

    private function buildSubstitution(string $daytime = 'am'): Substitution
    {
        return (new Substitution())
            ->setDate(new DateTimeImmutable('2022-04-01'))
            ->setDaytime($daytime);
    }

    private function buildClownAvailability(
        string $availability,
        bool $maxPlaysReached = false,
        string $gender = 'diverse',
    ): ClownAvailability {
        $clownAvailability = new ClownAvailability();
        $clownAvailability->setClown((new Clown())->setGender($gender));
        $clownAvailability->setMonth(Month::build('2022-04'));
        $clownAvailability->setMaxPlaysMonth(2);
        $clownAvailability->setCalculatedPlaysMonth($maxPlaysReached ? 2 : 1);
        $date = new DateTimeImmutable('2022-04-01');
        $clownAvailability->addClownAvailabilityTime($this->buildAvailabilityTimeSlot($availability, $date, 'am'));

        return $clownAvailability;
    }

    private function buildAvailabilityTimeSlot(string $availability, DateTimeInterface $date, string $daytime): ClownAvailabilityTime
    {
        $timeSlot = new ClownAvailabilityTime();
        $timeSlot->setAvailability($availability);
        $timeSlot->setDate($date);
        $timeSlot->setDaytime($daytime);

        return $timeSlot;
    }
}
