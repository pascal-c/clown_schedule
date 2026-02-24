<?php

declare(strict_types=1);

namespace App\Guard;

use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Repository\ConfigRepository;
use App\Repository\ScheduleRepository;
use App\Service\AuthService;
use App\Service\TimeService;
use App\Service\VenueService;
use App\Value\ScheduleStatus;

class PlayDateGuard
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private TimeService $timeService,
        private AuthService $authService,
        private ConfigRepository $configRepository,
        private VenueService $venueService,
    ) {
    }

    public function canDelete(PlayDate $_playDate): bool
    {
        return $this->authService->isAdmin();
    }

    public function canEdit(PlayDate $playDate): bool
    {
        return $this->canDelete($playDate);
    }

    public function canCancel(PlayDate $playDate): bool
    {
        if (!$this->authService->isAdmin()) {
            return false;
        }

        $month = $playDate->getMonth()->previous();

        return $playDate->isConfirmed() && $this->timeService->today() >= $month->getDate();
    }

    public function canAssign(PlayDate $playDate): bool
    {
        if ($this->authService->isAdmin()) {
            return true;
        }

        if (!$this->configRepository->find()->teamCanAssignPlayingClowns()) {
            return false;
        }

        $team = $this->venueService->getTeam($playDate->getVenue());

        return $team->contains($this->authService->getCurrentClown());
    }

    public function canMove(PlayDate $playDate): bool
    {
        if (!$this->authService->isAdmin()) {
            return false;
        }

        $month = $playDate->getMonth()->previous();

        return ($playDate->isCancelled() || $playDate->isConfirmed()) && $this->timeService->today() >= $month->getDate();
    }

    public function shouldNotDelete(PlayDate $playDate): bool
    {
        $month = $playDate->getMonth();
        $schedule = $this->scheduleRepository->find($month) ?? (new Schedule())->setMonth($month)->setStatus(ScheduleStatus::NOT_STARTED);

        return $schedule->isCompleted() || ($this->timeService->today() >= $playDate->getDate());
    }

    public function shouldNotEdit(PlayDate $playDate): bool
    {
        $isNew = null === $playDate->getId();
        if ($isNew) {
            return false;
        }

        return $this->shouldNotDelete($playDate);
    }
}
