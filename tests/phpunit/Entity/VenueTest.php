<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ClownVenuePreference;
use App\Entity\Venue;
use App\Entity\Fee;
use App\Value\Preference;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VenueTest extends TestCase
{
    public function testGetFeeFor(): void
    {
        $date1 = new DateTimeImmutable('2024-11-09');
        $date2 = new DateTimeImmutable('2024-11-07');
        $venue = new Venue();
        $venue->addFee($fee1 = (new Fee())->setValidFrom($date1));
        $venue->addFee($fee2 = (new Fee())->setValidFrom($date2));

        $this->assertSame($fee1, $venue->getFeeFor($date1->modify('+1 year')));
        $this->assertSame($fee1, $venue->getFeeFor($date1));
        $this->assertSame($fee2, $venue->getFeeFor($date1->modify('-1 day')));
        $this->assertSame($fee2, $venue->getFeeFor($date2));
        $this->assertNull($venue->getFeeFor($date2->modify('-1 day')));
    }

    public function testGetAveragePreference(): void
    {
        $venue = new Venue();
        $this->assertSame(Preference::OK, $venue->getAveragePreference());

        $venue->addClownVenuePreference(
            (new ClownVenuePreference())->setPreference(Preference::BEST)
        );
        $this->assertSame(Preference::BEST, $venue->getAveragePreference());

        $venue->addClownVenuePreference(
            (new ClownVenuePreference())->setPreference(Preference::BETTER)
        );
        $this->assertSame(Preference::BETTER, $venue->getAveragePreference());

        $venue->addClownVenuePreference(
            (new ClownVenuePreference())->setPreference(Preference::OK)
        );
        $this->assertSame(Preference::BETTER, $venue->getAveragePreference());

    }
}
