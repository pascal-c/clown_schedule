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
    public static function targetPlaysDataProvider(): array
    {
        return [
            [ // when sum of wishedPlays(36) is higher than totalPlays(31)
                array_map(fn (array $args) => self::buildClownAvailability(...$args), [[12, 8.8], [6, 10.0], [10, 8.0], [8, 4.2]]),
                [10, 6, 9, 6], // sum: 31
            ],
            [ // when sum of wishedPlays(24) is lower than totalPlays(31)
                array_map(fn (array $args) => self::buildClownAvailability(...$args), [[4, 8.8], [6, 10.0], [10, 8.0], [4, 4.2]]),
                [8, 9, 10, 4], // sum: 31
            ],
            [ // when sum of wishedPlays(24) is lower than totalPlays(31) and sum of MaxPlaysMonth(29) is lower than totalPlays(31)
                array_map(fn (array $args) => self::buildClownAvailability(...$args), [[4, 8.8, 5], [6, 10.0, 7], [10, 8.0, 12], [4, 4.2, 5]]),
                [5, 7, 12, 5], // sum: 29
            ],
        ];
    }

    private static function buildClownAvailability(int $wishedPlays, float $entitledPlays, int $maxPlaysMonth = 10): ClownAvailability
    {
        $clownAvailability = new ClownAvailability();
        $clownAvailability
            ->setEntitledPlaysMonth($entitledPlays)
            ->setWishedPlaysMonth($wishedPlays)
            ->setMaxPlaysMonth($maxPlaysMonth);

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
            self::buildClownAvailabilityWithTimeSlots(['yes' => 30]), // ratio 1
            self::buildClownAvailabilityWithTimeSlots(['yes' => 24, 'no' => 6]), // ratio 0.8
            self::buildClownAvailabilityWithTimeSlots(['yes' => 18, 'no' => 12]), // ratio 0.6
            self::buildClownAvailabilityWithTimeSlots(['yes' => 9, 'maybe' => 9, 'no' => 12]), // ratio 0.6
        ];
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateentitledPlays($clownAvailabilities, 6);

        // calculate entitled plays
        $this->assertEquals(2.0, $clownAvailabilities[0]->getEntitledPlaysMonth()); // 6 * 1.0 / 3
        $this->assertEquals(1.6, $clownAvailabilities[1]->getEntitledPlaysMonth()); // 6 * 0.8 / 3
        $this->assertEquals(1.2, $clownAvailabilities[2]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
        $this->assertEquals(1.2, $clownAvailabilities[3]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
    }

    private static function buildClownAvailabilityWithTimeSlots(array $timeSlots): ClownAvailability
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
