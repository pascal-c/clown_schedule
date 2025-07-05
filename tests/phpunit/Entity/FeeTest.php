<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Fee;
use PHPUnit\Framework\TestCase;

final class FeeTest extends TestCase
{
    public function testCopyFrom(): void
    {
        $oldFee = (new Fee())
            ->setFeeAlternative(2.42)
            ->setFeeStandard(3.14)
            ->setKilometers(10)
            ->setFeePerKilometer(0.42)
            ->setKilometersFeeForAllClowns(false);

        $newFee = new Fee();
        $result = $newFee->copyFrom($oldFee);

        $this->assertSame(2.42, $newFee->getFeeAlternative());
        $this->assertSame(3.14, $newFee->getFeeStandard());
        $this->assertSame(10, $newFee->getKilometers());
        $this->assertSame(0.42, $newFee->getFeePerKilometer());
        $this->assertFalse($newFee->isKilometersFeeForAllClowns());
        $this->assertSame($newFee, $result, 'copyFrom should return the same instance');
    }
}
