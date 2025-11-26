<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlayDate;
use App\Guard\PlayDateGuard;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Value\PlayDateChangeReason;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class PlayDateService
{
    public function __construct(
        private PlayDateGuard $playDateGuard,
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private EntityManagerInterface $entityManager,
        private PlayDateHistoryService $playDateHistoryService,
        private AuthService $authService,
    ) {
    }

    public function cancel(PlayDate $playDate): bool
    {
        if (!$this->playDateGuard->canCancel($playDate)) {
            return false;
        }

        $this->removeSubstitutionsIfNecessary($playDate);

        $playDate->cancel();
        $this->playDateHistoryService->add($playDate, $this->authService->getCurrentClown(), PlayDateChangeReason::CANCEL);

        return true;
    }

    public function move(PlayDate $playDate, FormInterface $form): bool
    {
        if (!$this->playDateGuard->canMove($playDate)) {
            return false;
        }

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

        return true;
    }

    public function create(PlayDate $playDate): void
    {
        $this->entityManager->persist($playDate);
        $this->playDateHistoryService->add($playDate, $this->authService->getCurrentClown(), PlayDateChangeReason::CREATE);
    }

    private function removeSubstitutionsIfNecessary(PlayDate $playDate): void
    {
        // remove substitutions if no other playdate exists at the same time slot
        $playDatesSameTimeSlot = $this->playDateRepository->findConfirmedByTimeSlotPeriod($playDate);
        if (1 === count($playDatesSameTimeSlot)) {
            foreach ($this->substitutionRepository->findByTimeSlotPeriod($playDate) as $substitution) {
                $this->entityManager->remove($substitution);
            }
        }
    }
}
