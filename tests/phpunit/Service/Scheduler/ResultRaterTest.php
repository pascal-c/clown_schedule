<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultRater;
use App\Service\Scheduler\Rater;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResultRaterTest extends TestCase
{
    private Rater|MockObject $rater;
    private ResultRater $resultComparator;

    public function setUp(): void
    {
        $this->rater = $this->createMock(Rater::class);
        $this->resultComparator = new ResultRater($this->rater);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCurrentPoints(int $rate, $expectedResult, int $playDatesCount = 2, bool $ignoreTargetPlays = false): void
    {
        $month = Month::build('2024-10');
        $result = Result::create($month)
            ->add(new PlayDate(), null)
            ->add(new PlayDate(), null)
        ;

        $this->rater->expects($this->once())
            ->method('totalPoints')
            ->with($month, $ignoreTargetPlays)
            ->willReturn($rate);

        $this->assertSame($expectedResult, $this->resultComparator->currentPoints($result, $playDatesCount));
    }

    public function dataProvider(): Generator
    {
        yield 'result is complete' => [
            'totalPoints' => 310,
            'expectedPoints' => 310,
        ];

        // with playDates missing
        yield '2 playDates are missing in result' => [
            'totalPoints' => 310,
            'expectedPoints' => 110,
            'playDatesCount' => 4,
            'ignoreTargetPlays' => true, // it should ignore target plays because there are more than 2 total playDates
        ];
    }
}
