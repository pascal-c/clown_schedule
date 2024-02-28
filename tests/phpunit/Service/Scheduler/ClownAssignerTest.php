<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Entity\Week;
use App\Repository\SubstitutionRepository;
use App\Service\PlayDateHistoryService;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use App\Value\PlayDateChangeReason;
use App\Value\TimeSlotPeriod;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class ClownAssignerTest extends TestCase
{
    public function firstClownDataProvider(): array
    {
        $buildResultSet = function (array $params): array {
            $clownAvailabilities = array_map(fn ($x) => $this->buildClownAvailability(), range(0, 3));
            $venue = new Venue();
            $venue->addPlayDate((new PlayDate())
                ->addPlayingClown($clownAvailabilities[0]->getClown())
                ->addPlayingClown($clownAvailabilities[1]->getClown()));
            $venue->addPlayDate((new PlayDate())
                ->addPlayingClown($clownAvailabilities[3]->getClown()));

            $expectedResultIndex = $params['expectedResultIndex'];

            return [
                $this->buildPlayDate($clownAvailabilities, $venue),
                $clownAvailabilities,
                'availableForResults' => $params['availableForResults'],
                'expectedResult' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
            ];
        };

        // the first two clowns are responsible for the venue
        return [
            $buildResultSet([ // second responsible clown is available
                'availableForResults' => [false, true, true, true],
                'expectedResultIndex' => 1,
            ]),
            $buildResultSet([ // both responsible clowns are available -> take clown that never played there before
                'availableForResults' => [true, true, true, true],
                'expectedResultIndex' => 1,
            ]),
            $buildResultSet([ // no responsible clown available take clown who played there most recently
                'availableForResults' => [false, false, true, true],
                'expectedResultIndex' => 3,
            ]),
            $buildResultSet([ // no clown available at all
                'availableForResults' => [false, false, false, false],
                'expectedResultIndex' => null,
            ]),
        ];
    }

    /**
     * @dataProvider firstClownDataProvider
     */
    public function testassignFirstClown(
        PlayDate $playDate,
        array $clownAvailabilities,
        array $availableForResults,
        ?ClownAvailability $expectedClownAvailability
    ): void {
        $clownAssigner = $this->buildClownAssigner($clownAvailabilities, $playDate, $availableForResults, $expectedClownAvailability);
        $clownAssigner->assignFirstClown($playDate, $clownAvailabilities);

        if (is_null($expectedClownAvailability)) {
            $this->assertTrue($playDate->getPlayingClowns()->isEmpty());
        } else {
            $this->assertSame($expectedClownAvailability->getClown(), $playDate->getPlayingClowns()->first());
        }

        foreach ($clownAvailabilities as $availability) {
            if ($availability === $expectedClownAvailability) {
                $this->assertSame(1, $availability->getCalculatedPlaysMonth());
            } else {
                $this->assertNull($availability->getCalculatedPlaysMonth());
            }
        }
    }

    public function secondClownDataProvider(): array
    {
        $buildResultSet = function (array $params): array {
            $clownAvailabilities = [
                $this->buildClownAvailability('no', targetPlays: 2, calculatedPlays: 1),
                $this->buildClownAvailability('maybe', targetPlays: 2, calculatedPlays: 1),
                $this->buildClownAvailability('yes', targetPlays: 2, calculatedPlays: 1),
                $this->buildClownAvailability('maybe', targetPlays: 4, calculatedPlays: 1),
                $this->buildClownAvailability('yes', targetPlays: 3, calculatedPlays: 1),
            ];
            $expectedResultIndex = $params['expectedResultIndex'];

            return [
                $this->buildPlayDate($clownAvailabilities)->addPlayingClown(new Clown()),
                $clownAvailabilities,
                'availableForResults' => $params['availableForResults'],
                'maxPlaysWeekReached' => $params['maxPlaysWeekReached'],
                'expectedResult' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
            ];
        };

        return [
            $buildResultSet([ // clowns with availability 'yes' available
                'availableForResults' => [false, true, true, true, true],
                'maxPlaysWeekReached' => [false, false, false, false, false],
                'expectedResultIndex' => 4,
            ]),
            $buildResultSet([ // only clowns with availability 'maybe' available
                'availableForResults' => [false, true, false, true, false],
                'maxPlaysWeekReached' => [false, false, false, false, false],
                'expectedResultIndex' => 3,
            ]),
            $buildResultSet([ // no clown available at all
                'availableForResults' => [false, false, false, false, false],
                'maxPlaysWeekReached' => [false, false, false, false, false],
                'expectedResultIndex' => null,
            ]),
            $buildResultSet([ // clowns with availability 'yes' available - but highest has maxPlaysWeekReached
                'availableForResults' => [false, true, true, true, true],
                'maxPlaysWeekReached' => [false, false, false, false, true],
                'expectedResultIndex' => 2,
            ]),
            $buildResultSet([ // no clowns with availability 'yes' and not maxPlaysWeekReached available
                'availableForResults' => [false, true, true, true, true],
                'maxPlaysWeekReached' => [false, false, true, false, true],
                'expectedResultIndex' => 3,
            ]),
            $buildResultSet([ // only clowns with maxPlaysWeekReached available
                'availableForResults' => [false, false, false, true, true],
                'maxPlaysWeekReached' => [false, false, false, true, true],
                'expectedResultIndex' => 4,
            ]),
        ];
    }

    /**
     * @dataProvider secondClownDataProvider
     */
    public function testAssignSecondClown(
        PlayDate $playDate,
        array $clownAvailabilities,
        array $availableForResults,
        array $maxPlaysWeekReached,
        ?ClownAvailability $expectedClownAvailability
    ): void {
        $clownAssigner = $this->buildClownAssigner($clownAvailabilities, $playDate, $availableForResults, $expectedClownAvailability, $maxPlaysWeekReached);
        $clownAssigner->assignSecondClown($playDate, $clownAvailabilities);

        if (is_null($expectedClownAvailability)) {
            $this->assertSame(1, $playDate->getPlayingClowns()->count());
        } else {
            $this->assertSame(2, $playDate->getPlayingClowns()->count());
            $this->assertSame($expectedClownAvailability->getClown(), $playDate->getPlayingClowns()->last());
        }

        foreach ($clownAvailabilities as $availability) {
            if ($availability === $expectedClownAvailability) {
                $this->assertSame(2, $availability->getCalculatedPlaysMonth());
            } else {
                $this->assertSame(1, $availability->getCalculatedPlaysMonth());
            }
        }
    }

    public function substitutionClownDataProvider(): array
    {
        $buildResultSet = function (array $availableOnResults, ?int $expectedResultIndex = null, $isAllDay = false, array $maxSubstitutionsWeekReached = [false, false, false, false, false]): array {
            $clownAvailabilities = [
                $this->buildClownAvailability('no', targetPlays: 0, calculatedPlays: 4),
                $this->buildClownAvailability('maybe', targetPlays: 0, calculatedPlays: 4),
                $this->buildClownAvailability('yes', targetPlays: 0, calculatedPlays: 4, availableAllDay: true),
                $this->buildClownAvailability('maybe', targetPlays: 0, calculatedPlays: 8, availableAllDay: true),
                $this->buildClownAvailability('yes', targetPlays: 0, calculatedPlays: 6, calculatedSubstitutions: 3),
            ];

            return [
                $clownAvailabilities,
                'availableOnResults' => $availableOnResults,
                'expectedResult' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
                'daytime' => $isAllDay ? TimeSlotPeriod::ALL : TimeSlotPeriod::AM,
                'maxSubstitutionsWeekReached' => $maxSubstitutionsWeekReached,
            ];
        };

        return [
            $buildResultSet(
                availableOnResults: [false, true, true, true, true],
                expectedResultIndex: 2,
            ),
            $buildResultSet( // only clowns with availability 'maybe' available
                availableOnResults: [false, true, false, true, false],
                expectedResultIndex: 3,
            ),
            $buildResultSet( // no clown available at all
                availableOnResults: [false, false, false, false, false],
                expectedResultIndex: null,
            ),
            $buildResultSet( // an all day event
                availableOnResults: [false, true, true, true, true],
                expectedResultIndex: 2,
                isAllDay: true,
            ),
            $buildResultSet( // clowns with availability 'yes' available - but they have maxSubstitutionsWeekReached
                availableOnResults: [false, true, true, true, true],
                maxSubstitutionsWeekReached: [false, false, true, false, true],
                expectedResultIndex: 3,
            ),
            $buildResultSet( // only clowns with maxPlaysWeekReached available
                availableOnResults: [false, true, true, true, true],
                maxSubstitutionsWeekReached: [false, true, true, true, true],
                expectedResultIndex: 2,
            ),
        ];
    }

    /**
     * @dataProvider substitutionClownDataProvider
     */
    public function testAssignSubstitutionClown(
        array $clownAvailabilities,
        array $availableOnResults,
        ?ClownAvailability $expectedClownAvailability,
        string $daytime,
        array $maxSubstitutionsWeekReached,
    ): void {
        $date = new DateTimeImmutable('2022-04-01');
        $availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableOn')
            ->willReturnCallback(function (TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $availability) use ($date, $daytime, $clownAvailabilities, $availableOnResults): bool {
                static $count = 0;
                $this->assertEquals(new TimeSlotPeriod($date, $daytime), $timeSlotPeriod);
                $this->assertSame($clownAvailabilities[$count], $availability);

                return $availableOnResults[$count++];
            });
        $availabilityChecker
            ->method('maxPlaysAndSubstitutionsWeekReached')
            ->willReturnCallback(function (Week $week, ClownAvailability $availability) use ($date, $clownAvailabilities, $maxSubstitutionsWeekReached): bool {
                $this->assertEquals(new Week($date), $week);
                $this->assertContains($availability, $clownAvailabilities);

                foreach ($clownAvailabilities as $key => $clownAvailability) {
                    if ($availability === $clownAvailability) {
                        return $maxSubstitutionsWeekReached[$key];
                    }
                }
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $substitution = new Substitution();
        if (!is_null($expectedClownAvailability)) {
            $substitutionRepository->expects($this->atMost(2))
                ->method('find')
                ->willReturn($substitution);
        }
        $playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $playDateHistoryService->expects($this->never())->method($this->anything());

        $clownAssigner = new ClownAssigner($availabilityChecker, $substitutionRepository, $entityManager, $playDateHistoryService);
        $clownAssigner->assignSubstitutionClown(new TimeSlotPeriod($date, $daytime), $clownAvailabilities);

        if (is_null($expectedClownAvailability)) {
            $this->assertNull($substitution->getSubstitutionClown());
        } else {
            $this->assertSame($expectedClownAvailability->getClown(), $substitution->getSubstitutionClown());
        }

        foreach ($clownAvailabilities as $key => $availability) {
            if ($availability === $expectedClownAvailability) {
                $this->assertSame(1, $availability->getCalculatedSubstitutions());
            } elseif (4 == $key) {
                $this->assertSame(3, $availability->getCalculatedSubstitutions());
            } else {
                $this->assertNull($availability->getCalculatedSubstitutions());
            }
        }
    }

    private function buildClownAssigner(
        array $clownAvailabilities,
        PlayDate $playDate,
        array $availableForResults,
        ?ClownAvailability $expectedClownAvailability,
        array $maxPlaysWeekReached = [false, false, false, false, false],
    ): ClownAssigner {
        $availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableFor')
            ->withConsecutive(
                ...array_map(
                    fn ($availability) => [
                        $this->identicalTo($playDate),
                        $this->identicalTo($availability),
                    ],
                    $clownAvailabilities
                )
            )
            ->willReturnOnConsecutiveCalls(...$availableForResults);

        $availabilityChecker
            ->method('maxPlaysWeekReached')
            ->willReturnCallback(
                function (Week $week, ClownAvailability $availability) use ($clownAvailabilities, $maxPlaysWeekReached): bool {
                    $this->assertEquals(new Week(new DateTimeImmutable('2022-03-28')), $week);
                    $this->assertContains($availability, $clownAvailabilities);

                    foreach ($clownAvailabilities as $key => $clownAvailability) {
                        if ($availability === $clownAvailability) {
                            return $maxPlaysWeekReached[$key];
                        }
                    }
                }
            );

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $substitutionRepository->expects($this->never())->method($this->anything());
        $playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        if (is_null($expectedClownAvailability)) {
            $playDateHistoryService->expects($this->never())->method($this->anything());
        } else {
            $playDateHistoryService->expects($this->once())->method('add')->with($playDate, null, PlayDateChangeReason::CALCULATION);
        }
        $clownAssigner = new ClownAssigner($availabilityChecker, $substitutionRepository, $entityManager, $playDateHistoryService);

        return $clownAssigner;
    }

    private function buildPlayDate(array $clownAvailabilites, Venue $venue = new Venue()): PlayDate
    {
        $venue->addResponsibleClown($clownAvailabilites[0]->getClown());
        $venue->addResponsibleClown($clownAvailabilites[1]->getClown());

        $playDate = new PlayDate();
        $playDate->setDate(new DateTimeImmutable('2022-04-01'));
        $playDate->setDaytime('am');
        $playDate->setVenue($venue);

        return $playDate;
    }

    private function buildClownAvailability(
        string $availability = 'yes',
        int $targetPlays = 2,
        ?int $calculatedPlays = null,
        ?int $calculatedSubstitutions = null,
        bool $availableAllDay = false
    ): ClownAvailability {
        static $counter = 0;
        $clownAvailability = new ClownAvailability();
        $clownAvailability->setClown((new Clown())->setName("Ulrike $counter av: $availability targetPlays: $targetPlays calcPlays: $calculatedPlays allDay: $availableAllDay"));
        $clownAvailability->setTargetPlays($targetPlays);
        $clownAvailability->setCalculatedPlaysMonth($calculatedPlays);
        $clownAvailability->setCalculatedSubstitutions($calculatedSubstitutions);
        $clownAvailability->addClownAvailabilityTime(
            (new ClownAvailabilityTime())
                ->setDate(new DateTimeImmutable('2022-04-01'))
                ->setDaytime('am')
                ->setAvailability($availability)
        );
        $clownAvailability->addClownAvailabilityTime(
            (new ClownAvailabilityTime())
                ->setDate(new DateTimeImmutable('2022-04-01'))
                ->setDaytime('pm')
                ->setAvailability($availableAllDay ? $availability : 'no')
        );

        ++$counter;

        return $clownAvailability;
    }
}
