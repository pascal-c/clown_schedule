<?php

namespace App\Repository;

use App\Entity\Month;
use App\Entity\Clown;
use App\Entity\ClownAvailability;

class ClownAvailabilityRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return ClownAvailability::class;
    }

    public function find(Month $month, Clown $clown): ?ClownAvailability
    {
        return $this->doctrineRepository->findOneBy(['month' => $month->getKey(), 'clown' => $clown]);
    }

    public function byMonth(Month $month): array
    {
        return $this->doctrineRepository->createQueryBuilder('ca')
            ->where('ca.month = :month')
            ->setParameter('month', $month->getKey())
            ->getQuery()
            ->enableResultCache()
            ->getResult();
    }
}
