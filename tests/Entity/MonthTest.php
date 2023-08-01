<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Month;
use DateTimeImmutable;

final class MonthTest extends TestCase
{
    public function testGetKey(): void
    {
        $month = Month::build('1978-12-24');
        $this->assertSame('1978-12', $month->getKey());
    }

    public function testBuild_now(): void
    {
        $month = Month::build('now');
        $this->assertSame((new DateTimeImmutable())->format('Y-m'), $month->getKey());
    }
}
