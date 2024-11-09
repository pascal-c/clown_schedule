<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Week;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAvailabilitySorter;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ClownAvailabilitySorterTest extends TestCase
{
    private const CLOWN1_FIRST = 'clown 1 comes first';
    private const CLOWN2_FIRST = 'clown 2 comes first';

    private AvailabilityChecker|MockObject $availabilityChecker;
    private ClownAvailabilitySorter $sorter;

    public function setUp(): void
    {
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->sorter = new ClownAvailabilitySorter($this->availabilityChecker);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSortForPlayDate(
        bool $maxPlaysWeekReached1,
        bool $maxPlaysWeekReached2,
        string $availability1,
        string $availability2,
        int $openTargetPlays1,
        int $openTargetPlays2,
        string $expectedOrder,
    ): void {
        $date = new DateTimeImmutable('now');
        $week = new Week($date);
        $playDate = (new PlayDate())->setDate($date);
        $clown1 = $this->createMock(ClownAvailability::class);
        $clown2 = $this->createMock(ClownAvailability::class);
        $clownAvailabilities = [$clown1, $clown2];

        $clown1->method('getAvailabilityOn')->with($playDate)->willReturn($availability1);
        $clown2->method('getAvailabilityOn')->with($playDate)->willReturn($availability2);

        $clown1->method('getOpenTargetPlays')->willReturn($openTargetPlays1);
        $clown2->method('getOpenTargetPlays')->willReturn($openTargetPlays2);

        $this->availabilityChecker->expects($this->exactly(2))
            ->method('maxPlaysWeekReached')->willReturnCallback(
                function (Week $checkedWeek, ClownAvailability $checkedClown) use ($week, $clown1, $clown2, $maxPlaysWeekReached1, $maxPlaysWeekReached2): bool {
                    $this->assertEquals($week, $checkedWeek);
                    $this->assertContains($checkedClown, [$clown1, $clown2]);

                    return ($clown1 === $checkedClown) ? $maxPlaysWeekReached1 : $maxPlaysWeekReached2;
                }
            );
        $sortedClownAvailabilities = $this->sorter->sortForPlayDate($playDate, $clownAvailabilities);
        if (self::CLOWN1_FIRST === $expectedOrder) {
            $this->assertSame([$clown1, $clown2], $sortedClownAvailabilities);
        } else {
            $this->assertSame([$clown2, $clown1], $sortedClownAvailabilities);
        }
    }

    public function dataProvider(): Generator
    {
        yield 'when max plays week reached for clown1' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => false,
            'availability1' => 'yes',
            'availability2' => 'maybe',
            'openTargetPlays1' => 10,
            'openTargetPlays2' => 1,

            'expectedFirstClown' => self::CLOWN2_FIRST,
        ];

        yield 'when max plays week reached for both' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => true,
            'availability1' => 'yes',
            'availability2' => 'maybe',
            'openTargetPlays1' => 10,
            'openTargetPlays2' => 1,

            'expectedFirstClown' => self::CLOWN1_FIRST,
        ];

        yield 'when max plays week reached for nobody and availability is yes for clown2 and maybe for clown1' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => true,
            'availability1' => 'maybe',
            'availability2' => 'yes',
            'openTargetPlays1' => 10,
            'openTargetPlays2' => 1,

            'expectedFirstClown' => self::CLOWN2_FIRST,
        ];

        yield 'when max plays week reached for nobody and availability is yes for clown1 and maybe for clown2' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => true,
            'availability1' => 'yes',
            'availability2' => 'maybe',
            'openTargetPlays1' => 1,
            'openTargetPlays2' => 10,

            'expectedFirstClown' => self::CLOWN1_FIRST,
        ];

        yield 'when max plays week reached for nobody and availability is same and clown1 has more open plays' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => true,
            'availability1' => 'yes',
            'availability2' => 'yes',
            'openTargetPlays1' => 10,
            'openTargetPlays2' => 1,

            'expectedFirstClown' => self::CLOWN1_FIRST,
        ];

        yield 'when max plays week reached for nobody and availability is same and clown2 has more open plays' => [
            'maxPlaysWeekReached1' => true,
            'maxPlaysWeekReached2' => true,
            'availability1' => 'maybe',
            'availability2' => 'maybe',
            'openTargetPlays1' => 1,
            'openTargetPlays2' => 10,

            'expectedFirstClown' => self::CLOWN2_FIRST,
        ];
    }
}
