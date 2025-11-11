<?php

declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Service\Scheduler\FairPlayCalculator;
use Codeception\Stub;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('targetPlaysDataProvider')]
    public function testcalculateTargetPlays(array $clownAvailabilities, array $expectedTargetPlays): void
    {
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateTargetPlays($clownAvailabilities, 31);

        foreach ($clownAvailabilities as $key => $availability) {
            assertEquals($expectedTargetPlays[$key], $availability->getTargetPlays());
        }
    }

    public function testCalculateEntitledPlays(): void
    {
        $clownAvailabilities = [
            (new ClownAvailability())->setAvailabilityRatio(1.0), // ratio 1
            (new ClownAvailability())->setAvailabilityRatio(0.8), // ratio 0.8
            (new ClownAvailability())->setAvailabilityRatio(0.6), // ratio 0.6
            (new ClownAvailability())->setAvailabilityRatio(0.6), // ratio 0.6
        ];
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateEntitledPlays($clownAvailabilities, 6);

        // calculate entitled plays
        $this->assertEquals(2.0, $clownAvailabilities[0]->getEntitledPlaysMonth()); // 6 * 1.0 / 3
        $this->assertEquals(1.6, $clownAvailabilities[1]->getEntitledPlaysMonth()); // 6 * 0.8 / 3
        $this->assertEquals(1.2, $clownAvailabilities[2]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
        $this->assertEquals(1.2, $clownAvailabilities[3]->getEntitledPlaysMonth()); // 6 * 0.6 / 3
    }

    public function testCalculateAvailabilityRatios(): void
    {
        $clownAvailabilities = [
            Stub::make(ClownAvailability::class, ['isAvailableOn' => Stub::consecutive(true, true, true, true)]),
            Stub::make(ClownAvailability::class, ['isAvailableOn' => Stub::consecutive(true, true, true, false)]),
            Stub::make(ClownAvailability::class, ['isAvailableOn' => Stub::consecutive(true, true, false, false)]),
            Stub::make(ClownAvailability::class, ['isAvailableOn' => Stub::consecutive(true, false, false, false)]),
            Stub::make(ClownAvailability::class, ['isAvailableOn' => Stub::consecutive(false, false, false, false)]),
        ];
        $playDates = [
            new PlayDate(),
            new PlayDate(),
            new PlayDate(),
            new PlayDate(),
        ];

        // when play dates are empty
        $fairPlayCalculator = new FairPlayCalculator();
        $fairPlayCalculator->calculateAvailabilityRatios($clownAvailabilities, []);
        foreach ($clownAvailabilities as $availability) {
            $this->assertNull($availability->getAvailabilityRatio());
        }

        // when play dates are not empty
        $fairPlayCalculator->calculateAvailabilityRatios($clownAvailabilities, $playDates);

        $this->assertEquals(1.0, $clownAvailabilities[0]->getAvailabilityRatio());
        $this->assertEquals(0.75, $clownAvailabilities[1]->getAvailabilityRatio());
        $this->assertEquals(0.5, $clownAvailabilities[2]->getAvailabilityRatio());
        $this->assertEquals(0.25, $clownAvailabilities[3]->getAvailabilityRatio());
        $this->assertEquals(0.0, $clownAvailabilities[4]->getAvailabilityRatio());

    }
}
