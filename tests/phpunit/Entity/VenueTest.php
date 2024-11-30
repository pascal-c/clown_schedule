<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Venue;
use App\Entity\VenueFee;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VenueTest extends TestCase
{
    public function testGetFeeFor(): void
    {
        $date1 = new DateTimeImmutable('2024-11-09');
        $date2 = new DateTimeImmutable('2024-11-07');
        $venue = new Venue();
        $venue->addFee($fee1 = (new VenueFee())->setValidFrom($date1));
        $venue->addFee($fee2 = (new VenueFee())->setValidFrom($date2));

        $this->assertSame($fee1, $venue->getFeeFor($date1->modify('+1 year')));
        $this->assertSame($fee1, $venue->getFeeFor($date1));
        $this->assertSame($fee2, $venue->getFeeFor($date1->modify('-1 day')));
        $this->assertSame($fee2, $venue->getFeeFor($date2));
        $this->assertNull($venue->getFeeFor($date2->modify('-1 day')));
    }
}
