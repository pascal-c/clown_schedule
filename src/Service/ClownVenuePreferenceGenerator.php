<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Clown;
use App\Entity\ClownVenuePreference;
use App\Repository\VenueRepository;
use App\Value\Preference;

class ClownVenuePreferenceGenerator
{
    public function __construct(private VenueRepository $venueRepository)
    {
    }

    public function generateMissingPreferences(Clown $clown): void
    {
        $venues = $this->venueRepository->active();

        foreach ($venues as $venue) {
            if (!$clown->getClownVenuePreferenceFor($venue)) {
                $clownVenuePreference = new ClownVenuePreference();
                $clownVenuePreference
                    ->setVenue($venue)
                    ->setPreference(Preference::OK);
                $clown->addClownVenuePreference($clownVenuePreference);
            }
        }
    }
}
