<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Service\Scheduler\FairPlayCalculator;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

final class FairPlayCalculatorTest extends TestCase
{
    public function targetPlaysDataProvider(): array
    {
        return [
            [
                array_map(fn (array $args) => $this->buildClownAvailability(...$args), [[12, 8.8], [6, 10.0], [10, 8.0], [8, 4.2]]),
                [10, 6, 9, 6],
            ],
            [
                array_map(fn (array $args) => $this->buildClownAvailability(...$args), [[4, 8.8], [6, 10.0], [10, 8.0], [4, 4.2]]),
                [8, 9, 10, 4],
            ],
        ];
    }

    private function buildClownAvailability(int $wishedPlays, float $entitledPlays): ClownAvailability
    {
        $clownAvailability = new ClownAvailability();
        $clownAvailability->setEntitledPlaysMonth($entitledPlays);
        $clownAvailability->setWishedPlaysMonth($wishedPlays);

        return $clownAvailability;
    }

    /**
     * @dataProvider targetPlaysDataProvider
     */
    public function testcalculateTargetPlays(array $clownAvailabilities, array $expectedTargetPlays): void
    {
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateTargetPlays($clownAvailabilities, 31);

        foreach ($clownAvailabilities as $key => $availability) {
            assertEquals($expectedTargetPlays[$key], $availability->getTargetPlays());
        }
    }

    public function testcalculateEntitledPlays(): void
    {
        $clownAvailabilities = [
            $this->buildClownAvailabilityWithTimeSlots(['yes' => 30]), // ratio 1
            $this->buildClownAvailabilityWithTimeSlots(['yes' => 24, 'no' => 6]), // ratio 0.8
            $this->buildClownAvailabilityWithTimeSlots(['yes' => 18, 'no' => 12]), // ratio 0.6
            $this->buildClownAvailabilityWithTimeSlots(['yes' => 9, 'maybe' => 9, 'no' => 12]), // ratio 0.6
        ];
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateentitledPlays($clownAvailabilities, 6);

        // calculate entitled plays
        $this->assertEquals(2.0, $clownAvailabilities[0]->getEntitledPlaysMonth()); // 6 * 1.0 / 3
        $this->assertEquals(1.6, $clownAvailabilities[1]->getEntitledPlaysMonth()); // 6 * 0.8 / 3
        $this->assertEquals(1.2, $clownAvailabilities[2]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
        $this->assertEquals(1.2, $clownAvailabilities[3]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
    }

    private function buildClownAvailabilityWithTimeSlots(array $timeSlots): ClownAvailability
    {
        $clownAvailability = new ClownAvailability();
        foreach ($timeSlots as $availability => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $timeSlot = new ClownAvailabilityTime();
                $timeSlot->setAvailability($availability);
                $clownAvailability->addClownAvailabilityTime($timeSlot);
            }
        }

        return $clownAvailability;
    }
}
