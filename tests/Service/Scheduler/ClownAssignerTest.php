<?php declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use PHPUnit\Framework\TestCase;

final class ClownAssignerTest extends TestCase
{
    public function firstClownDataProvider(): array
    {
        $buildResultSet = function(array $params): array {
            $clownAvailabilities = array_map(fn($x) => $this->buildClownAvailability(), range(0, 3));
            $venue = new Venue;
            $venue->addPlayDate((new PlayDate)
                ->addPlayingClown($clownAvailabilities[0]->getClown())
                ->addPlayingClown($clownAvailabilities[1]->getClown()));
            $venue->addPlayDate((new PlayDate)
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
        ?ClownAvailability $expectedClownAvailability): void
    {
        $availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableFor')
            ->withConsecutive(
                ...array_map(
                    fn($availability) => [
                        $this->identicalTo($playDate), 
                        $this->identicalTo($availability),
                    ],
                    $clownAvailabilities
                )
            )
            ->willReturnOnConsecutiveCalls(...$availableForResults);

        $clownAssigner = new ClownAssigner($availabilityChecker);
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
        $buildResultSet = function(array $params): array {
            $clownAvailabilities = [
                $this->buildClownAvailability('no', 2.5, 1),
                $this->buildClownAvailability('maybe', 2.5, 1),
                $this->buildClownAvailability('yes', 2.5, 1),
                $this->buildClownAvailability('maybe', 4.5, 1),
                $this->buildClownAvailability('yes', 3.5, 1),
            ];
            $expectedResultIndex = $params['expectedResultIndex'];
            
            return [
                $this->buildPlayDate($clownAvailabilities)->addPlayingClown(new Clown),
                $clownAvailabilities,
                'availableForResults' => $params['availableForResults'],
                'expectedResult' => $expectedResultIndex ? $clownAvailabilities[$expectedResultIndex] : null,
            ]; 
        };

        return [
            $buildResultSet([
                'availableForResults' => [false, true, true, true, true],
                'expectedResultIndex' => 4,
            ]),
            $buildResultSet([ # only clowns with availability 'maybe' available
                'availableForResults' => [false, true, false, true, false],
                'expectedResultIndex' => 3,
            ]),
            $buildResultSet([ // no clown available at all
                'availableForResults' => [false, false, false, false, false],
                'expectedResultIndex' => null,
            ]),
        ];
    }

    /**
     * @dataProvider secondClownDataProvider
     */
    public function testassignSecondClown(
        PlayDate $playDate, 
        array $clownAvailabilities, 
        array $availableForResults, 
        ?ClownAvailability $expectedClownAvailability): void
    {
        $availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $availabilityChecker->expects($this->exactly(count($clownAvailabilities)))
            ->method('isAvailableFor')
            ->withConsecutive(
                ...array_map(
                    fn($availability) => [
                        $this->identicalTo($playDate), 
                        $this->identicalTo($availability),
                    ],
                    $clownAvailabilities
                )
            )
            ->willReturnOnConsecutiveCalls(...$availableForResults);

        $clownAssigner = new ClownAssigner($availabilityChecker);
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


    private function buildPlayDate(array $clownAvailabilites, Venue $venue = new Venue): PlayDate
    {
        $venue->addResponsibleClown($clownAvailabilites[0]->getClown());
        $venue->addResponsibleClown($clownAvailabilites[1]->getClown());

        $playDate = new PlayDate;
        $playDate->setDate(new \DateTimeImmutable('2022-04-01'));
        $playDate->setDaytime('am');
        $playDate->setVenue($venue);
        return $playDate;
    }
    
    private function buildClownAvailability(string $availability = 'yes', float $entitledPlays = 2.5, ?int $calculatedPlays = null): ClownAvailability
    {
        $timeSlot = (new ClownAvailabilityTime)
            ->setDate(new \DateTimeImmutable('2022-04-01'))
            ->setDaytime('am')
            ->setAvailability($availability);

        $clownAvailability = new ClownAvailability;
        $clownAvailability->setClown(new Clown);
        $clownAvailability->setEntitledPlaysMonth($entitledPlays);
        $clownAvailability->setCalculatedPlaysMonth($calculatedPlays);
        $clownAvailability->addClownAvailabilityTime($timeSlot);
        return $clownAvailability;
    }
}
