<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Value\PlayDateChangeReason;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class PlayDateService
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private EntityManagerInterface $entityManager,
        private PlayDateHistoryService $playDateHistoryService,
        private AuthService $authService,
        private ArrayCache $cache,
        private ScheduleRepository $scheduleRepository,
    ) {
    }

    public function cancel(PlayDate $playDate): void
    {
        $this->removeSubstitutionsIfNecessary($playDate);

        $playDate->cancel();
        $this->playDateHistoryService->add($playDate, $this->authService->getCurrentClown(), PlayDateChangeReason::CANCEL);
    }

    public function move(PlayDate $playDate, FormInterface $form): void
    {
        $this->removeSubstitutionsIfNecessary($playDate);
        $newPlayDate = new PlayDate();
        $newPlayDate
            ->setVenue($playDate->getVenue())
            ->setTitle($playDate->getTitle())
            ->setIsSuper($playDate->isSuper())
            ->setType($playDate->getType())
            ->setFee($playDate->getPlayDateFee())
            ->setRecurringDate($playDate->getRecurringDate())
            ->setDate($form->get('date')->getData())
            ->setDaytime($form->get('daytime')->getData())
            ->setMeetingTime($form->get('meetingTime')->getData())
            ->setPlayTimeFrom($form->get('playTimeFrom')->getData())
            ->setPlayTimeTo($form->get('playTimeTo')->getData())
        ;
        foreach ($playDate->getPlayingClowns() as $clown) {
            $newPlayDate->addPlayingClown($clown);
        }

        $playDate->move($newPlayDate);
        $this->playDateHistoryService->add($playDate, $this->authService->getCurrentClown(), PlayDateChangeReason::MOVE);
        $this->create($newPlayDate);
    }

    public function create(PlayDate $playDate): void
    {
        $this->entityManager->persist($playDate);
        $this->playDateHistoryService->add($playDate, $this->authService->getCurrentClown(), PlayDateChangeReason::CREATE);
    }

    public function assign(PlayDate $playDate, array $clowns, ?PlayDateChangeReason $changeReason = null): void
    {
        $schedule = $this->scheduleRepository->find($playDate->getMonth());
        $changeReason ??= $schedule?->isCompleted()
            ? PlayDateChangeReason::MANUAL_CHANGE
            : PlayDateChangeReason::MANUAL_CHANGE_FOR_SCHEDULE;

        // assign clowns to all playdates in the bundle (or just the single playdate if it is not bundled)
        $playDates = $playDate->hasBundle() ? $playDate->getBundle()->getPlayDates() : [$playDate];
        foreach ($playDates as $bundledPlayDate) {
            $bundledPlayDate->getPlayingClowns()->clear();
            foreach ($clowns as $clown) {
                $bundledPlayDate->addPlayingClown($clown);
            }
            $this->playDateHistoryService->add($bundledPlayDate, $this->authService->getCurrentClown(), $changeReason);
        }
    }

    private function removeSubstitutionsIfNecessary(PlayDate $playDate): void
    {
        // remove substitutions if no other playdate exists at the same time slot
        $playDatesSameTimeSlot = $this->playDateRepository->findConfirmedByTimeSlotPeriod($playDate);
        if (1 === count($playDatesSameTimeSlot)) {
            foreach ($this->substitutionRepository->findByTimeSlotPeriod($playDate) as $substitution) {
                $this->entityManager->remove($substitution);
                $this->cache->remove($this->substitutionRepository->byMonthCacheKey($substitution->getMonth()));
            }
        }
    }
}
