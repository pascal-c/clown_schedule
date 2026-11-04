<?php

namespace App\Repository;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Service\TimeService;
use App\Value\ScheduleStatus;

class ScheduleRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    protected function getEntityName(): string
    {
        return Schedule::class;
    }

    public function find(Month $month): Schedule
    {
        return $this->doctrineRepository->createQueryBuilder('schedule')
            ->where('schedule.month = :month')
            ->setParameter('month', $month->getKey())
            ->getQuery()
            ->enableResultCache(1)
            ->getOneOrNullResult()
            ?? $this->buildSchedule($month);
    }

    private function buildSchedule(Month $month): Schedule
    {
        $schedule = new Schedule();
        $schedule->setMonth($month);
        $schedule->setStatus(
            $this->timeService->firstOfMonth() > $month->getDate() ? ScheduleStatus::COMPLETED : ScheduleStatus::NOT_STARTED,
        );

        return $schedule;
    }
}
