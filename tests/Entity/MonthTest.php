<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Month;

final class MonthTest extends TestCase
{
    public function testgetKey(): void
    {
        $month = new Month(new \DateTimeImmutable('1978-12-24'));
        $this->assertSame('1978-12', $month->getKey());
    }
}
