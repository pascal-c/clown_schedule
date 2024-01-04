<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Month;
use PHPUnit\Framework\TestCase;

final class MonthTest extends TestCase
{
    public function testGetKey(): void
    {
        $month = Month::build('1978-12-24');
        $this->assertSame('1978-12', $month->getKey());
    }

    public function testBuildNow(): void
    {
        $month = Month::build('now');
        $this->assertSame((new \DateTimeImmutable())->format('Y-m'), $month->getKey());
    }
}
