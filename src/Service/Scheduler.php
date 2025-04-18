<?php

namespace App\Service;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Gateway\RosterCalculator\RosterResult;
use App\Gateway\RosterCalculator\RosterResultApplier;
use App\Gateway\RosterCalculatorGateway;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use App\Service\Scheduler\FairPlayCalculator;
use App\Service\Scheduler\PlayDateSorter;
use App\Value\ScheduleStatus;
use App\Value\TimeSlotPeriod;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\ORM\EntityManagerInterface;

class Scheduler
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ClownAssigner $clownAssigner,
        private AvailabilityChecker $availabilityChecker,
        private FairPlayCalculator $fairPlayCalculator,
        private SubstitutionRepository $substitutionRepository,
        private ScheduleRepository $scheduleRepository,
        private EntityManagerInterface $entityManager,
        private PlayDateSorter $playDateSorter,
        private RosterCalculatorGateway $rosterCalculatorGateway,
        private RosterResultApplier $rosterResultApplier,
    ) {
    }

    public function calculate(Month $month, bool $calculateComplex): RosterResult
    {
        $timeSlotPeriods = [];
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $playDates = $this->playDateRepository->regularByMonth($month);
        $this->removeClownAssignments($playDates, $clownAvailabilities, $month);
        $playDates = $this->playDateSorter->sortByAvailabilities(
            $playDates,
            $clownAvailabilities,
        );

        foreach ($playDates as $playDate) {
            $this->clownAssigner->assignFirstClown($playDate, $clownAvailabilities);
            if (!in_array([$playDate->getDate(), $playDate->getDaytime()], $timeSlotPeriods)
                && !in_array([$playDate->getDate(), TimeSlotPeriodInterface::ALL], $timeSlotPeriods)) {
                $timeSlotPeriods[] = [$playDate->getDate(), $playDate->getDaytime()];
            }
        }

        $this->fairPlayCalculator->calculateEntitledPlays($clownAvailabilities, count($playDates) * 2);
        $this->fairPlayCalculator->calculateTargetPlays($clownAvailabilities, count($playDates) * 2);

        if ($calculateComplex) {
            $result = $this->rosterCalculatorGateway->calcuate($playDates, $clownAvailabilities);
            $this->rosterResultApplier->apply($result, $month);
        } else {
            $result = $this->clownAssigner->assignSecondClowns($month, $playDates, $clownAvailabilities, takeFirst: true);
        }

        foreach ($timeSlotPeriods as $timeSlot) {
            $this->clownAssigner->assignSubstitutionClown(new TimeSlotPeriod($timeSlot[0], $timeSlot[1]), $clownAvailabilities);
        }

        return $result;
    }

    public function complete(Month $month): ?Schedule
    {
        $schedule = $this->scheduleRepository->find($month);

        if (null === $schedule) {
            $schedule = (new Schedule())->setMonth($month);
            $this->entityManager->persist($schedule);
        }

        if (ScheduleStatus::COMPLETED === $schedule->getStatus()) {
            return null;
        }

        $playDates = $this->playDateRepository->regularByMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month, indexedByClown: true);
        $substitutionTimeSlots = $this->substitutionRepository->byMonth($month);

        foreach ($clownAvailabilities as $clownAvailability) {
            $clownAvailability
                ->setScheduledPlaysMonth(0)
                ->setScheduledSubstitutions(0);
        }
        foreach ($playDates as $playDate) {
            foreach ($playDate->getPlayingClowns() as $clown) {
                if (array_key_exists($clown->getId(), $clownAvailabilities)) {
                    $clownAvailabilities[$clown->getId()]->incScheduledPlaysMonth();
                }
            }
        }
        foreach ($substitutionTimeSlots as $substitutionTimeSlot) {
            $clown = $substitutionTimeSlot->getSubstitutionClown();
            if (!is_null($clown) && array_key_exists($clown->getId(), $clownAvailabilities)) {
                $clownAvailabilities[$clown->getId()]->incScheduledSubstitutions();
            }
        }

        $schedule->setStatus(ScheduleStatus::COMPLETED);

        return $schedule;
    }

    private function removeClownAssignments(array $playDates, array $clownAvailabilities, Month $month): void
    {
        foreach ($playDates as $playDate) {
            foreach ($playDate->getPlayingClowns() as $clown) {
                $playDate->removePlayingClown($clown);
            }
        }

        foreach ($clownAvailabilities as $availability) {
            $availability->setCalculatedPlaysMonth(null);
            $availability->setCalculatedSubstitutions(null);
        }

        foreach ($this->substitutionRepository->byMonth($month) as $timeSlot) {
            $timeSlot->setSubstitutionClown(null);
        }
    }
}
