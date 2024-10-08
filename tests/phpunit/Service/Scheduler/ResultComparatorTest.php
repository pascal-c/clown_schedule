<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Month;
use App\Service\Scheduler\Result;
use App\Service\Scheduler\ResultComparator;
use App\Service\Scheduler\ResultRater;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResultComparatorTest extends TestCase
{
    private ResultRater|MockObject $resultRater;
    private ResultComparator $resultComparator;

    public function setUp(): void
    {
        $this->resultRater = $this->createMock(ResultRater::class);
        $this->resultComparator = new ResultComparator($this->resultRater);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsDefinitelyWorseThan(int $rate1, bool $expectedComparison): void
    {
        $month = Month::build('2024-10');
        $result1 = Result::create($month);
        $result2 = Result::create($month);

        $this->resultRater->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function (Result $result, bool $ignoreTargetPlays) use ($result1, $result2, $rate1): int {
                if ($result === $result1) {
                    $this->assertTrue($ignoreTargetPlays);

                    return $rate1;
                }
                if ($result === $result2) {
                    $this->assertFalse($ignoreTargetPlays);

                    return 10;
                }
            });

        $this->assertSame($expectedComparison, $this->resultComparator->isDefinitelyWorseThan($result1, $result2));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsWorseThan(int $rate1, bool $expectedComparison): void
    {
        $month = Month::build('2024-10');
        $result1 = Result::create($month);
        $result2 = Result::create($month);

        $this->resultRater->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function (Result $result, bool $ignoreTargetPlays) use ($result1, $result2, $rate1): int {
                $this->assertContains($result, [$result1, $result2]);

                if ($result === $result1) {
                    $this->assertFalse($ignoreTargetPlays);

                    return $rate1;
                }
                if ($result === $result2) {
                    $this->assertFalse($ignoreTargetPlays);

                    return 10;
                }
            });

        $this->assertSame($expectedComparison, $this->resultComparator->isWorseThan($result1, $result2));
    }

    public function dataProvider(): Generator
    {
        yield 'rate1 is higher than rate2' => [
            'rate1' => 11,
            'expectedComparison' => true,
        ];

        yield 'rate1 is equal to rate2' => [
            'rate1' => 10,
            'expectedComparison' => false,
        ];

        yield 'rate1 is lower than rate2' => [
            'rate1' => 9,
            'expectedComparison' => false,
        ];
    }
}
