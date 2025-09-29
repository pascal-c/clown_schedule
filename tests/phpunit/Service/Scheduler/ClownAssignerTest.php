<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Entity\Week;
use App\Gateway\RosterCalculator\RosterResult;
use App\Repository\SubstitutionRepository;
use App\Service\PlayDateHistoryService;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\BestPlayingClownCalculator;
use App\Service\Scheduler\ClownAssigner;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultApplier;
use App\Value\PlayDateChangeReason;
use App\Value\TimeSlotPeriod;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

final class ClownAssignerTest extends TestCase
{
    private AvailabilityChecker&MockObject $availabilityChecker;
    private SubstitutionRepository&MockObject $substitutionRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private PlayDateHistoryService&MockObject $playDateHistoryService;
    private BestPlayingClownCalculator&MockObject $bestPlayingClownCalculator;
    private ResultApplier&MockObject $resultApplier;

    private ClownAssigner $clownAssigner;

    public function setUp(): void
    {
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $this->bestPlayingClownCalculator = $this->createMock(BestPlayingClownCalculator::class);
        $this->resultApplier = $this->createMock(ResultApplier::class);

        $this->clownAssigner = new ClownAssigner(
            $this->availabilityChecker,
            $this->substitutionRepository,
            $this->entityManager,
            $this->playDateHistoryService,
            $this->bestPlayingClownCalculator,
            $this->resultApplier,
        );
    }

    public static function firstClownDataProvider(): array
    {
        $buildResultSet = function (array $params): array {
            $clownAvailabilities = array_map(fn ($x) => self::buildClownAvailability(), range(0, 3));
            $venue = new Venue();
            $venue->addPlayDate((new PlayDate())
                ->addPlayingClown($clownAvailabilities[0]->getClown())
                ->addPlayingClown($clownAvailabilities[1]->getClown()));
            $venue->addPlayDate((new PlayDate())
                ->addPlayingClown($clownAvailabilities[3]->getClown()));

            $expectedResultIndex = $params['expectedResultIndex'];

            return [
                self::buildPlayDate($clownAvailabilities, $venue),
                $clownAvailabilities,
                'availableForResults' => $params['availableForResults'],
                'expectedClownAvailability' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
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

    #[DataProvider('firstClownDataProvider')]
    public function testAssignFirstClown(
        PlayDate $playDate,
        array $clownAvailabilities,
        array $availableForResults,
        ?ClownAvailability $expectedClownAvailability,
    ): void {

        $this->availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableFor')
            ->with($playDate, $this->isInstanceOf(ClownAvailability::class))
            ->willReturnOnConsecutiveCalls(...$availableForResults);

        $this->availabilityChecker
            ->method('maxPlaysWeekReached')
            ->willReturnCallback(
                function (Week $week, ClownAvailability $availability) use ($clownAvailabilities): bool {
                    $this->assertEquals(new Week(new DateTimeImmutable('2022-03-28')), $week);
                    $this->assertContains($availability, $clownAvailabilities);

                    return false;
                }
            );

        $this->substitutionRepository->expects($this->never())->method($this->anything());
        if (is_null($expectedClownAvailability)) {
            $this->playDateHistoryService->expects($this->never())->method($this->anything());
        } else {
            $this->playDateHistoryService->expects($this->once())->method('add')->with($playDate, null, PlayDateChangeReason::CALCULATION);
        }

        $this->bestPlayingClownCalculator->expects($this->never())->method($this->anything());
        $this->resultApplier->expects($this->never())->method($this->anything());
        $this->clownAssigner->assignFirstClown($playDate, $clownAvailabilities);

        if (is_null($expectedClownAvailability)) {
            $this->assertTrue($playDate->getPlayingClowns()->isEmpty());
        } else {
            $this->assertSame($expectedClownAvailability->getClown(), $playDate->getPlayingClowns()->first());
        }
    }

    public function testAssignFirstClownWhenAlreadyAssigned(): void
    {
        $clownAvailabilities = array_map(fn ($_) => self::buildClownAvailability(), range(0, 3));
        $assignedClown = $clownAvailabilities[3]->getClown();
        $playDate = self::buildPlayDate($clownAvailabilities);
        $playDate->addPlayingClown($assignedClown);

        $this->availabilityChecker
            ->expects($this->never())
            ->method($this->anything());
        $this->playDateHistoryService->expects($this->never())->method($this->anything());

        $this->clownAssigner->assignFirstClown($playDate, $clownAvailabilities);
        $this->assertSame(1, $playDate->getPlayingClowns()->count());
        $this->assertSame($assignedClown, $playDate->getPlayingClowns()->first());
    }

    public function testAssignSecondClownsWithoutOnlyFirst(): void
    {
        $this->availabilityChecker->expects($this->never())->method($this->anything());
        $this->substitutionRepository->expects($this->never())->method($this->anything());
        $this->entityManager->expects($this->never())->method($this->anything());

        $month = Month::build('2024-11');
        $playDates = [new PlayDate(), new PlayDate()];
        $clownAvailabilites = [];
        $firstResult = Result::create($month)->setPoints(42);
        $allResults = [
            $bestResult = Result::create($month)->setPoints(41),
            Result::create($month)->setPoints(43),
        ];
        $this->bestPlayingClownCalculator
            ->expects($this->once())
            ->method('onlyFirst')
            ->with($month, $playDates, $clownAvailabilites)
            ->willReturn($firstResult);
        $this->bestPlayingClownCalculator
            ->expects($this->once())
            ->method('__invoke')
            ->with($month, $playDates, $clownAvailabilites, 42, 2)
            ->willReturn($allResults);
        $this->resultApplier
            ->expects($this->once())
            ->method('applyResult')
            ->with($this->identicalTo($bestResult));
        $this->playDateHistoryService
            ->expects($this->exactly(2))
            ->method('add')
            ->with($this->anything(), null, PlayDateChangeReason::CALCULATION);

        $result = $this->clownAssigner->assignSecondClowns($month, $playDates, $clownAvailabilites, takeFirst: false);
        $this->assertEquals(new RosterResult(rating: ['total' => 41], firstResultTotalPoints: 42, counter: 1), $result);
    }

    public function testAssignSecondClownsWithOnlyFirst(): void
    {
        $this->availabilityChecker->expects($this->never())->method($this->anything());
        $this->substitutionRepository->expects($this->never())->method($this->anything());
        $this->entityManager->expects($this->never())->method($this->anything());

        $month = Month::build('2024-11');
        $playDates = [new PlayDate(), new PlayDate()];
        $clownAvailabilites = [];
        $firstResult = Result::create($month)->setPoints(42);

        $this->bestPlayingClownCalculator
            ->expects($this->once())
            ->method('onlyFirst')
            ->with($month, $playDates, $clownAvailabilites)
            ->willReturn($firstResult);
        $this->bestPlayingClownCalculator
            ->expects($this->never())
            ->method('__invoke');
        $this->resultApplier
            ->expects($this->once())
            ->method('applyResult')
            ->with($this->identicalTo($firstResult));
        $this->playDateHistoryService
            ->expects($this->exactly(2))
            ->method('add')
            ->with($this->anything(), null, PlayDateChangeReason::CALCULATION);

        $result = $this->clownAssigner->assignSecondClowns($month, $playDates, $clownAvailabilites, takeFirst: true);
        $this->assertEquals(new RosterResult(rating: ['total' => 42], firstResultTotalPoints: 42, counter: 1), $result);
    }

    public static function substitutionClownDataProvider(): array
    {
        $buildResultSet = function (array $availableOnResults, ?int $expectedResultIndex = null, $isAllDay = false, array $maxSubstitutionsWeekReached = [false, false, false, false, false]): array {
            $clownAvailabilities = [
                self::buildClownAvailability('no', targetPlays: 0, calculatedPlays: 4),
                self::buildClownAvailability('maybe', targetPlays: 0, calculatedPlays: 4),
                self::buildClownAvailability('yes', targetPlays: 0, calculatedPlays: 4, availableAllDay: true),
                self::buildClownAvailability('maybe', targetPlays: 0, calculatedPlays: 8, availableAllDay: true),
                self::buildClownAvailability('yes', targetPlays: 0, calculatedPlays: 6, calculatedSubstitutions: 3),
            ];

            return [
                $clownAvailabilities,
                'availableOnResults' => $availableOnResults,
                'expectedClownAvailability' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
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

    #[DataProvider('substitutionClownDataProvider')]
    public function testAssignSubstitutionClown(
        array $clownAvailabilities,
        array $availableOnResults,
        ?ClownAvailability $expectedClownAvailability,
        string $daytime,
        array $maxSubstitutionsWeekReached,
    ): void {
        $date = new DateTimeImmutable('2022-04-01');
        $this->availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableForSubstitution')
            ->willReturnCallback(function (TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $availability) use ($date, $daytime, $clownAvailabilities, $availableOnResults): bool {
                static $count = 0;
                $this->assertEquals(new TimeSlotPeriod($date, $daytime), $timeSlotPeriod);
                $this->assertSame($clownAvailabilities[$count], $availability);

                return $availableOnResults[$count++];
            });
        $this->availabilityChecker
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

        $substitution = new Substitution();
        $substitution2 = new Substitution();
        if (!is_null($expectedClownAvailability)) {
            $this->substitutionRepository->expects($this->exactly('all' === $daytime ? 2 : 1))
                ->method('find')
                ->willReturnOnConsecutiveCalls($substitution, $substitution2);
        }
        $this->playDateHistoryService->expects($this->never())->method($this->anything());
        $this->bestPlayingClownCalculator->expects($this->never())->method($this->anything());
        $this->resultApplier->expects($this->never())->method($this->anything());

        $this->clownAssigner->assignSubstitutionClown(new TimeSlotPeriod($date, $daytime), $clownAvailabilities);

        if (is_null($expectedClownAvailability)) {
            $this->assertNull($substitution->getSubstitutionClown());
        } else {
            $this->assertSame($expectedClownAvailability->getClown(), $substitution->getSubstitutionClown());
            if ('all' === $daytime) {
                $this->assertSame($expectedClownAvailability->getClown(), $substitution2->getSubstitutionClown());
            } else {
                $this->assertNull($substitution2->getSubstitutionClown());
            }
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

    public function testAssignSubstitutionClownWhenSubstitutionIsAlreadySet(): void
    {
        $date = new DateTimeImmutable('2022-04-01');
        $clownAvailabilities = [
            self::buildClownAvailability(calculatedSubstitutions: 2),
            self::buildClownAvailability(),
            self::buildClownAvailability(),
        ];
        $alreadyAssignedClown = $clownAvailabilities[0]->getClown();
        $this->availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableForSubstitution')
            ->willReturn(true);
        $this->availabilityChecker
            ->method('maxPlaysAndSubstitutionsWeekReached')
            ->willReturn(false);

        $substitution = new Substitution();
        $substitution->setSubstitutionClown($alreadyAssignedClown);
        $this->substitutionRepository->expects($this->exactly(1))
            ->method('find')
            ->willReturn($substitution);

        $this->clownAssigner->assignSubstitutionClown(new TimeSlotPeriod($date, 'am'), $clownAvailabilities);

        $this->assertSame($alreadyAssignedClown, $substitution->getSubstitutionClown());

        // make sure calculatedSubstitutions is not increased
        foreach ($clownAvailabilities as $key => $availability) {
            if (0 == $key) {
                $this->assertSame(2, $availability->getCalculatedSubstitutions());
            } else {
                $this->assertNull($availability->getCalculatedSubstitutions());
            }
        }
    }

    private static function buildPlayDate(array $clownAvailabilites, Venue $venue = new Venue()): PlayDate
    {
        $venue->addResponsibleClown($clownAvailabilites[0]->getClown());
        $venue->addResponsibleClown($clownAvailabilites[1]->getClown());

        $playDate = new PlayDate();
        $playDate->setDate(new DateTimeImmutable('2022-04-01'));
        $playDate->setDaytime('am');
        $playDate->setVenue($venue);

        return $playDate;
    }

    private static function buildClownAvailability(
        string $availability = 'yes',
        int $targetPlays = 2,
        ?int $calculatedPlays = null,
        ?int $calculatedSubstitutions = null,
        bool $availableAllDay = false,
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
