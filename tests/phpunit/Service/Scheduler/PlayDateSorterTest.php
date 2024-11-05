<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\PlayDateSorter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PlayDateSorterTest extends TestCase
{
    private AvailabilityChecker|MockObject $availabilityChecker;
    private PlayDateSorter $playDateSorter;

    public function setUp(): void
    {
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->playDateSorter = new PlayDateSorter($this->availabilityChecker);
    }

    public function test(): void
    {
        $playDates = [$playDate1 = new PlayDate(), $playDate2 = new PlayDate()];
        $clownAvailabilities = [new ClownAvailability(), new ClownAvailability()];
        $this->availabilityChecker->method('isAvailableFor')->willReturnCallback(
            function (PlayDate $playDate, ClownAvailability $_) use ($playDate1): bool {
                return $playDate === $playDate1; // for playDate2 no clown is available
            }
        );
        $sortedPlayDates = $this->playDateSorter->sortByAvailabilities($playDates, $clownAvailabilities);
        $this->assertSame([$playDate2, $playDate1], $sortedPlayDates);
    }
}
