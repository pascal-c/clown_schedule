<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Venue;
use App\Repository\ConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class VenueService
{
    public function __construct(
        private ConfigRepository $configRepository,
    ) {
    }

    public function getTeam(?Venue $venue): Collection
    {
        if (!$this->configRepository->isFeatureTeamsActive() || !$venue?->isTeamActive()) {
            return new ArrayCollection();
        }

        return $venue->getTeam();
    }

    public function hasTeam(?Venue $venue): bool
    {
        return $this->getTeam($venue)->count() > 0;
    }
}
