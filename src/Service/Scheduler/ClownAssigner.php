<?php

namespace App\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Repository\SubstitutionRepository;
use App\Service\PlayDateHistoryService;
use App\Service\Scheduler\AvailabilityChecker\MaxPlaysReachedChecker;
use App\Value\PlayDateChangeReason;
use App\Value\TimeSlotPeriod;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ClownAssigner
{
    public function __construct(
        private AvailabilityChecker $availabilityChecker,
        private MaxPlaysReachedChecker $maxPlaysReachedChecker,
        private SubstitutionRepository $substitutionRepository,
        private EntityManagerInterface $entityManager,
        private PlayDateHistoryService $playDateHistoryService,
    ) {
    }

    public function assignFirstClown(PlayDate $playDate, array $clownAvailabilities): void
    {
        if (!$playDate->getPlayingClowns()->isEmpty()) {
            return;
        }

        $availableClownAvailabilities = $this->getAvailabilitiesFor($playDate, $clownAvailabilities);

        $availableResponsibleClownAvailabilities = array_filter(
            $availableClownAvailabilities,
            fn (ClownAvailability $availability) => $playDate->getVenue()->getResponsibleClowns()->contains($availability->getClown())
        );
        if (count($availableResponsibleClownAvailabilities) > 1) {
            $clownAvailability = $this->clownWithMostAncientPlay($playDate->getVenue(), $availableResponsibleClownAvailabilities);
        } elseif (1 == count($availableResponsibleClownAvailabilities)) {
            $clownAvailability = array_pop($availableResponsibleClownAvailabilities);
        } else {
            if (empty($availableClownAvailabilities)) {
                return;
            }

            $clownAvailability = $this->clownWithMostRecentPlay($playDate->getVenue(), $availableClownAvailabilities);
        }

        $this->assignClown($playDate, $clownAvailability);
    }

    public function assignSubstitutionClown(TimeSlotPeriod $timeSlotPeriod, array $clownAvailabilities): void
    {
        $availableClownAvailabilities = array_filter(
            $clownAvailabilities,
            fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableForSubstitution($timeSlotPeriod, $availability)
        );
        if (empty($availableClownAvailabilities)) {
            return;
        }

        usort(
            $availableClownAvailabilities,
            function (ClownAvailability $availability1, ClownAvailability $availability2) use ($timeSlotPeriod) {
                // when maxPlayWeek ist reached, the clown comes last
                $a1MaxSubstitutionsWeekReached = $this->maxPlaysReachedChecker->maxPlaysAndSubstitutionsWeekReached($timeSlotPeriod->getWeek(), $availability1);
                $a2MaxPlaysWeekReached = $this->maxPlaysReachedChecker->maxPlaysAndSubstitutionsWeekReached($timeSlotPeriod->getWeek(), $availability2);
                if ($a1MaxSubstitutionsWeekReached !== $a2MaxPlaysWeekReached) {
                    return $a1MaxSubstitutionsWeekReached ? 1 : -1;
                }

                // when availability is the same, take clown with more open substitutions first
                $a1Availability = $availability1->getAvailabilityOn($timeSlotPeriod);
                $a2Availability = $availability2->getAvailabilityOn($timeSlotPeriod);
                if ($a1Availability == $a2Availability) {
                    return
                        $availability2->getOpenSubstitutions()
                        <=>
                        $availability1->getOpenSubstitutions();
                }

                // prefer clown with availability 'yes' before clown with availability 'maybe'
                return 'yes' == $a1Availability ? -1 : 1;
            }
        );

        $clownAvailability = $availableClownAvailabilities[0];
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            $upserted = $this->upsertSubstitution($timeSlot->getDate(), $timeSlot->getDaytime(), $clownAvailability->getClown());
        }
        if ($upserted) {
            $clownAvailability->incCalculatedSubstitutions();
        }
    }

    private function upsertSubstitution(DateTimeImmutable $date, string $daytime, Clown $clown): bool
    {
        $substitution = $this->substitutionRepository->find($date, $daytime);
        if (is_null($substitution)) {
            $substitution = (new Substitution())->setDate($date)->setDaytime($daytime);
            $this->entityManager->persist($substitution);
        }
        if ($substitution->getSubstitutionClown()) {
            return false;
        }

        $substitution->setSubstitutionClown($clown);

        return true;
    }

    private function assignClown(PlayDate $playDate, ClownAvailability $clownAvailability): void
    {
        $playDate->addPlayingClown($clownAvailability->getClown());
        $this->playDateHistoryService->add($playDate, null, PlayDateChangeReason::CALCULATION);
    }

    private function getAvailabilitiesFor(PlayDate $playDate, array $clownAvailabilities)
    {
        return array_filter(
            $clownAvailabilities,
            fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableFor($playDate, $availability)
        );
    }

    private function clownWithMostAncientPlay(Venue $venue, array $clownAvailabilities): ClownAvailability
    {
        foreach (array_reverse($venue->getPlayDates()->getValues()) as $playDate) {
            foreach ($playDate->getPlayingClowns() as $playingClown) {
                $clownAvailabilities = array_filter(
                    $clownAvailabilities,
                    fn ($availability) => $availability->getClown() !== $playingClown
                );
                if (1 == count($clownAvailabilities)) {
                    return array_pop($clownAvailabilities);
                }
            }
        }

        return $clownAvailabilities[array_rand($clownAvailabilities)];
    }

    private function clownWithMostRecentPlay(Venue $venue, array $clownAvailabilities): ClownAvailability
    {
        foreach (array_reverse($venue->getPlayDates()->getValues()) as $playDate) {
            foreach ($playDate->getPlayingClowns() as $playingClown) {
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
