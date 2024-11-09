<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Week;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class WeekTest extends TestCase
{
    public function testGetId(): void
    {
        $week = new Week(new DateTimeImmutable('2017-01-01'));
        $this->assertSame('2016-52', $week->getId());

        $week = new Week(new DateTimeImmutable('2017-01-02'));
        $this->assertSame('2017-01', $week->getId());
    }
}
