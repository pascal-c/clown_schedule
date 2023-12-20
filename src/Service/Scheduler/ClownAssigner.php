<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\PlayDateHistory;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Repository\SubstitutionRepository;
use App\Service\PlayDateHistoryService;
use App\Service\Scheduler\AvailabilityChecker;
use App\Value\PlayDateChangeReason;
use App\Value\TimeSlotPeriod;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ClownAssigner
{
    public function __construct(
        private AvailabilityChecker $availabilityChecker, 
        private SubstitutionRepository $substitutionRepository,
        private EntityManagerInterface $entityManager,
        private PlayDateHistoryService $playDateHistoryService,
        ) {}

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

    public function assignSubstitutionClown(TimeSlotPeriod $timeSlotPeriod, array $clownAvailabilities): void
    {
        $availableClownAvailabilities = array_filter(
            $clownAvailabilities,
            fn(ClownAvailability $availability) => $this->availabilityChecker->isAvailableOn($timeSlotPeriod, $availability)
        );
        if (empty($availableClownAvailabilities)) {
            return;
        }

        usort(
            $availableClownAvailabilities, 
            function(ClownAvailability $availability1, ClownAvailability $availability2) use ($timeSlotPeriod)
            {
                $a1Availability = $availability1->getAvailabilityOn($timeSlotPeriod);
                $a2Availability = $availability2->getAvailabilityOn($timeSlotPeriod);
                if ($a1Availability == $a2Availability) {
                    return 
                        $availability2->getOpenSubstitutions()
                        <=>
                        $availability1->getOpenSubstitutions();
                }

                return $a1Availability == 'yes' ? -1 : 1;
            }
        );

        $clownAvailability = $availableClownAvailabilities[0];
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            $this->upsertSubstitution($timeSlot->getDate(), $timeSlot->getDaytime(), $clownAvailability->getClown());
        }

        $clownAvailability->incCalculatedSubstitutions();
    }

    private function upsertSubstitution(DateTimeImmutable $date, string $daytime, Clown $clown): void
    {
        $substitution = $this->substitutionRepository->find($date, $daytime);
        if (is_null($substitution)) {
            $substitution = (new Substitution)->setDate($date)->setDaytime($daytime);
            $this->entityManager->persist($substitution);
        }

        $substitution->setSubstitutionClown($clown);
    }

    private function assignClown(PlayDate $playDate, ClownAvailability $clownAvailability): void
    {
        $playDate->addPlayingClown($clownAvailability->getClown());
        $clownAvailability->incCalculatedPlaysMonth();
        $this->playDateHistoryService->add($playDate, null, PlayDateChangeReason::CALCULATION);
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
                
                $a1Availability = $availability1->getAvailabilityOn($playDate);
                $a2Availability = $availability2->getAvailabilityOn($playDate);
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
