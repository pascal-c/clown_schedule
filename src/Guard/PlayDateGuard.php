<?php

declare(strict_types=1);

namespace App\Guard;

use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use App\Service\TimeService;
use App\Value\ScheduleStatus;

class PlayDateGuard
{
    public function __construct(private ScheduleRepository $scheduleRepository, private TimeService $timeService)
    {
    }

    public function canDelete(PlayDate $playDate): bool
    {
        $month = $playDate->getMonth();
        $schedule = $this->scheduleRepository->find($month) ?? (new Schedule())->setMonth($month)->setStatus(ScheduleStatus::NOT_STARTED);

        return !$schedule->isCompleted() && ($this->timeService->today() < $playDate->getDate());
    }

    public function canEdit(PlayDate $playDate): bool
    {
        $isNew = null === $playDate->getId();

        return $isNew || $this->canDelete($playDate);
    }

    public function canCancel(PlayDate $playDate): bool
    {
        $month = $playDate->getMonth()->previous();

        return $playDate->isConfirmed() && $this->timeService->today() >= $month->getDate();
    }

    public function canMove(PlayDate $playDate): bool
    {
        $month = $playDate->getMonth()->previous();

        return ($playDate->isCancelled() || $playDate->isConfirmed()) && $this->timeService->today() >= $month->getDate();
    }
}
