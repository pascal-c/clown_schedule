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

        $this->calculator = new BestPlayingClownCalculator(
            $this->availabilityChecker,
            $this->clownAvailabilitySorter,
            $this->resultApplier,
            $this->resultUnapplier,
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
        $this->resultApplier->expects($this->exactly(4))->method('__invoke'); // 1 time for the empty result and 3 times for the 3 result of first playDate
        $this->resultUnapplier->expects($this->exactly(4))->method('__invoke');

        $month = Month::build('2101-12');
        $results = ($this->calculator)($month, $this->playDates, $this->clownAvailabilities);

        $this->assertSame(6, count($results));

        $expectedResults = [ // Thorsten is not available for playDate 2
            Result::create($month)->add($this->playDate1, $this->fernando)->add($this->playDate2, $this->fernando),
            Result::create($month)->add($this->playDate1, $this->fernando)->add($this->playDate2, null),
            Result::create($month)->add($this->playDate1, $this->thorsten)->add($this->playDate2, $this->fernando),
            Result::create($month)->add($this->playDate1, $this->thorsten)->add($this->playDate2, null),
            Result::create($month)->add($this->playDate1, null)->add($this->playDate2, $this->fernando),
            Result::create($month)->add($this->playDate1, null)->add($this->playDate2, null),
        ];

        $this->assertEquals($expectedResults, $results);
    }

    public function testOnlyFirst(): void
    {
        $this->resultApplier->expects($this->exactly(2))->method('__invoke'); // 2 playDates, so 2 applies
        $this->resultUnapplier->expects($this->exactly(2))->method('__invoke');

        $month = Month::build('2101-12');
        $result = $this->calculator->onlyFirst($month, $this->playDates, $this->clownAvailabilities);

        $expectedResults = // Thorsten is not available for playDate 2
            Result::create($month)->add($this->playDate1, $this->fernando)->add($this->playDate2, $this->fernando);
        $this->assertEquals($expectedResults, $result);
    }
}
