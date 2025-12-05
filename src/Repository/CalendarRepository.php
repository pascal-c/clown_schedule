<?php

namespace App\Repository;

use App\Entity\Calendar;
use Doctrine\ORM\EntityRepository;

class CalendarRepository extends AbstractRepository
{
    protected EntityRepository $doctrineRepository;

    protected function getEntityName(): string
    {
        return Calendar::class;
    }

    public function findByUuid(string $uuid): ?Calendar
    {
        return $this->doctrineRepository->findOneBy(['uuid' => $uuid]);
    }
}
