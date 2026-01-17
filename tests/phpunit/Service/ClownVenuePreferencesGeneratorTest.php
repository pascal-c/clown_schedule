<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\Venue;
use App\Repository\VenueRepository;
use App\Service\ClownVenuePreferenceGenerator;
use App\Value\Preference;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class ClownVenuePreferencesGeneratorTest extends TestCase
{
    private ClownVenuePreferenceGenerator $clownVenuePreferenceGenerator;
    private VenueRepository&MockObject $venueRepository;

    public function setUp(): void
    {
        $this->venueRepository = $this->createMock(VenueRepository::class);
        $this->clownVenuePreferenceGenerator = new ClownVenuePreferenceGenerator($this->venueRepository);
    }

    public function testGenerateMissing(): void
    {
        $clown = new Clown();
        $venue1 = new Venue();
        $venue2 = new Venue();
        $this->venueRepository
            ->method('active')
            ->willReturn([$venue1, $venue2]);
        $this->clownVenuePreferenceGenerator->generateMissingPreferences($clown);

        $this->assertCount(2, $clown->getClownVenuePreferences());
        $this->assertSame($venue1, $clown->getClownVenuePreferences()->first()->getVenue());
        $this->assertSame($venue2, $clown->getClownVenuePreferences()->last()->getVenue());

        $this->assertSame(Preference::OK, $clown->getClownVenuePreferences()->first()->getPreference());
        $this->assertSame(Preference::OK, $clown->getClownVenuePreferences()->last()->getPreference());
    }
}
