<?php

namespace App\Repository;

use App\Entity\Month;
use App\Entity\Schedule;

class ScheduleRepository extends AbstractRepository
{

    protected function getEntityName(): string
    {
        return Schedule::class;
    }

    public function find(Month $month): ?Schedule
    {
        return $this->doctrineRepository->createQueryBuilder('schedule')
            ->where('schedule.month = :month')
            ->setParameter('month', $month->getKey())
            ->getQuery()
            ->enableResultCache(1)
            ->getOneOrNullResult()
            ;
    }
}
