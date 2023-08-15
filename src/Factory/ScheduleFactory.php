<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Value\ScheduleStatus;

class ScheduleFactory extends AbstractFactory
{
    public function create(Month $month, ScheduleStatus $status = null)
    {
        $status ??= ScheduleStatus::IN_PROGRESS;

        $schedule = (new Schedule)
            ->setMonth($month)
            ->setStatus($status)
            ;

        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
        return $schedule;
    }
}
