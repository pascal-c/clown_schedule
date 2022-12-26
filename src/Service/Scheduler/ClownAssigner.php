<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Service\Scheduler\AvailabilityChecker;

class ClownAssigner
{
    public function __construct(private AvailabilityChecker $availabilityChecker)
    {
    }

    public function assignFirstClown(PlayDate $playDate, array $clownAvailabilities): void
    {
        $availableClownAvailabilities = $this->getAvailabilitiesFor($playDate, $clownAvailabilities);

        $availableResponsibleClownAvailabilities = array_filter(
            $availableClownAvailabilities,
            fn(ClownAvailability $availability) => $playDate->getVenue()->getResponsibleClowns()->contains($availability->getClown())
        );
        if (count($availableResponsibleClownAvailabilities) > 1) {
            $clownAvailability = $this->clownWithMostAncientPlay($playDate->getVenue(), $availableResponsibleClownAvailabilities);
        } elseif (count($availableResponsibleClownAvailabilities) == 1) {
            $clownAvailability = array_pop($availableResponsibleClownAvailabilities);
        } else {
            if (empty($availableClownAvailabilities)) { return; }
            
            $clownAvailability = $this->clownWithMostRecentPlay($playDate->getVenue(), $availableClownAvailabilities);
        }

        $this->assignClown($playDate, $clownAvailability);
    }

    public function assignSecondClown(PlayDate $playDate, array $clownAvailabilities): void
    {
        $availableClownAvailabilities = $this->getAvailabilitiesFor($playDate, $clownAvailabilities);
        if (empty($availableClownAvailabilities)) {
            return;
        }
        
        $orderedClownAvailabilities = $this->orderAvailabilitesFor($playDate, $availableClownAvailabilities);
        $this->assignClown($playDate, $orderedClownAvailabilities[0]);
    }

    private function assignClown(PlayDate $playDate, ClownAvailability $clownAvailability): void
    {
        $playDate->addPlayingClown($clownAvailability->getClown());
        $clownAvailability->incCalculatedPlaysMonth();
    }

    private function getAvailabilitiesFor(PlayDate $playDate, array $clownAvailabilities)
    {
        return array_filter(
            $clownAvailabilities,
            fn(ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate, $availability)
        );
    
    }

    private function orderAvailabilitesFor(PlayDate $playDate, array $clownAvailabilities): array
    {
        usort(
            $clownAvailabilities, 
            function(ClownAvailability $availability1, ClownAvailability $availability2) use ($playDate)
            {
                
                $a1Availability = $availability1->getAvailabilityOn($playDate->getDate(), $playDate->getDaytime());
                $a2Availability = $availability2->getAvailabilityOn($playDate->getDate(), $playDate->getDaytime());
                if ($a1Availability == $a2Availability) {
                    return 
                        $availability2->getOpenTargetPlays()
                        <=>
                        $availability1->getOpenTargetPlays();
                }

                return $a1Availability == 'yes' ? -1 : 1;
            }
        );

        return $clownAvailabilities;
    }

    private function clownWithMostAncientPlay(Venue $venue, array $clownAvailabilities): ClownAvailability
    {
        foreach(array_reverse($venue->getPlayDates()->getValues()) as $playDate) {
            foreach($playDate->getPlayingClowns() as $playingClown) {
                
                $clownAvailabilities = array_filter($clownAvailabilities, 
                    fn($availability) => $availability->getClown() !== $playingClown
                );
                if (count($clownAvailabilities) == 1) {
                    return array_pop($clownAvailabilities);
                }
            }
        }

        return $clownAvailabilities[array_rand($clownAvailabilities)];
    }

    private function clownWithMostRecentPlay(Venue $venue, array $clownAvailabilities): ClownAvailability
    {
        foreach(array_reverse($venue->getPlayDates()->getValues()) as $playDate) {
            foreach($playDate->getPlayingClowns() as $playingClown) {
                foreach ($clownAvailabilities as $availability) {
                    if ($availability->getClown() === $playingClown) {
                        return $availability;
                    }
                }
            }
        }
        
        // none of the clowns ever played there, so take any clown
        return $clownAvailabilities[array_rand($clownAvailabilities)];
    }
}
