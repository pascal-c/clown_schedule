<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\BestPlayingClownCalculator;
use App\Service\Scheduler\ClownAvailabilitySorter;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultApplier;
use App\Service\Scheduler\ResultRater;
use App\Service\Scheduler\ResultUnapplier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BestPlayingClownCalculatorTest extends TestCase
{
    private BestPlayingClownCalculator $calculator;
    private AvailabilityChecker|MockObject $availabilityChecker;
    private ClownAvailabilitySorter|MockObject $clownAvailabilitySorter;
    private ResultApplier|MockObject $resultApplier;
    private ResultUnapplier|MockObject $resultUnapplier;
    private ResultRater|MockObject $resultRater;

    private array $playDates;
    private PlayDate $playDate1;
    private PlayDate $playDate2;
    private array $clownAvailabilities;
    private ClownAvailability $fernando;
    private ClownAvailability $thorsten;

    public function setUp(): void
    {
        // services
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->clownAvailabilitySorter = $this->createMock(ClownAvailabilitySorter::class);
        $this->resultApplier = $this->createMock(ResultApplier::class);
        $this->resultUnapplier = $this->createMock(ResultUnapplier::class);
        $this->resultRater = $this->createMock(ResultRater::class);

        $this->calculator = new BestPlayingClownCalculator(
            $this->availabilityChecker,
            $this->clownAvailabilitySorter,
            $this->resultApplier,
            $this->resultUnapplier,
            $this->resultRater,
        );

        // data
        $this->playDates = [
            $this->playDate1 = (new PlayDate())->setId(1)->setTitle('1'),
            $this->playDate2 = (new PlayDate())->setId(2)->setTitle('2'),
        ];
        $this->clownAvailabilities = [
            $this->fernando = (new ClownAvailability())->setClown((new Clown())->setName('Fernando')),
            $this->thorsten = (new ClownAvailability())->setClown((new Clown())->setName('Thorsten')),
        ];
        $this->availabilityChecker->method('isAvailableFor')->willReturnCallback( // Thorsten is not available for playDate 2
            fn (PlayDate $playDate, ClownAvailability $availability) => 2 !== $playDate->getId() || $availability !== $this->thorsten
        );
        $this->clownAvailabilitySorter->method('sortForPlayDate')->willReturnCallback(
            fn (PlayDate $_, array $clownAvailabilities) => $clownAvailabilities
        );
    }

    public function test(): void
    {
        $this->resultApplier->expects($this->exactly(3))->method('applyResult'); // 1 time for first playDate and 2 times for second playDate
        $this->resultUnapplier->expects($this->exactly(3))->method('unapplyResult');
        $this->resultApplier->expects($this->exactly(4))->method('applyAssignment'); // [fernando], [thorsten], [fernando, fernando], [thorsten, fernando]
        $this->resultUnapplier->expects($this->exactly(4))->method('unapplyAssignment');

        $month = Month::build('2101-12');
        $firstResultRate = 317;

        $this->resultRater
            ->expects($this->exactly(7)) // 3 times for first playDate and 2x2 times for second playDate
            ->method('currentPoints')
            ->willReturnCallback(
                function (Result $checkedResult, int $checkedPlayDatesCount) use ($month): int {
                    $this->assertSame($month, $checkedResult->getMonth());
                    $this->assertSame(2, $checkedPlayDatesCount);

                    static $count = 0;
                    ++$count;

                    return (3 === $count) ? 317 : 316; // null for first play date is definetely worse, everything else not
                }
            );

        $results = ($this->calculator)($month, $this->playDates, $this->clownAvailabilities, $firstResultRate, 2);

        $this->assertSame(4, count($results));

        $expectedResults = [ // Thorsten is not available for playDate 2
            Result::create($month)->add($this->playDate1, $this->fernando)->add($this->playDate2, $this->fernando)->setPoints(316),
            Result::create($month)->add($this->playDate1, $this->fernando)->add($this->playDate2, null)->setPoints(316),
            Result::create($month)->add($this->playDate1, $this->thorsten)->add($this->playDate2, $this->fernando)->setPoints(316),
            Result::create($month)->add($this->playDate1, $this->thorsten)->add($this->playDate2, null)->setPoints(316),
        ];

        $this->assertEquals($expectedResults, $results);
    }

    public function testOnlyFirst(): void
    {
        $this->resultApplier->expects($this->never())->method('applyResult');
        $this->resultApplier->expects($this->exactly(2))->method('applyAssignment'); // 2 playDates, so 2 applies
        $this->resultUnapplier->expects($this->exactly(1))->method('unapplyResult'); // 1 time at the end
        $this->resultUnapplier->expects($this->never())->method('unapplyAssignment');
        $this->resultRater
            ->expects($this->once())
            ->method('currentPoints')
            ->willReturn(22);

        $month = Month::build('2101-12');
        $result = $this->calculator->onlyFirst($month, $this->playDates, $this->clownAvailabilities);

        $expectedResult = // Thorsten is not available for playDate 2
            Result::create($month)
                ->add($this->playDate1, $this->fernando)
                ->add($this->playDate2, $this->fernando)
                ->setPoints(22);
        $this->assertEquals($expectedResult, $result);
    }
}
