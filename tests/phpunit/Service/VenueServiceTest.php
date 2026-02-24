<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Venue;
use App\Repository\ConfigRepository;
use App\Service\VenueService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Generator;

class VenueServiceTest extends TestCase
{
    private ConfigRepository&MockObject $configRepository;
    private VenueService $venueService;

    public function setUp(): void
    {
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->venueService = new VenueService($this->configRepository);
        parent::setUp();
    }

    #[DataProvider('getTeamProvider')]
    public function testGetTeam(?Venue $venue, bool $featureActive = true, bool $venueTeamActive = true, array $expectedTeam = []): void
    {
        $this->configRepository->expects($this->once())->method('isFeatureTeamsActive')->willReturn($featureActive);
        if (null !== $venue) {
            $venue->setTeamActive($venueTeamActive);
        }
        $result = $this->venueService->getTeam($venue);

        $this->assertEquals($expectedTeam, $result->toArray());
    }

    public static function getTeamProvider(): Generator
    {
        $venue = new Venue();
        $team = $venue->getTeam();
        $team->add('clown1');
        $team->add('clown2');

        yield 'when feature is active and venue team is active' => [
            'venue' => $venue,
            'expectedTeam' => ['clown1', 'clown2'],
        ];

        yield 'when feature is inactive' => [
            'venue' => $venue,
            'featureActive' => false,
            'expectedTeam' => [],
        ];

        yield 'when venue team is inactive' => [
            'venue' => $venue,
            'venueTeamActive' => false,
            'expectedTeam' => [],
        ];

        yield 'when venue is null' => [
            'venue' => null,
            'expectedTeam' => [],
        ];
    }

    #[DataProvider('getTeamProvider')]
    public function testHasTeam(?Venue $venue, bool $featureActive = true, bool $venueTeamActive = true, array $expectedTeam = []): void
    {
        $this->configRepository->expects($this->once())->method('isFeatureTeamsActive')->willReturn($featureActive);
        if (null !== $venue) {
            $venue->setTeamActive($venueTeamActive);
        }
        $result = $this->venueService->hasTeam($venue);

        $this->assertEquals(!empty($expectedTeam), $result);
    }
}
