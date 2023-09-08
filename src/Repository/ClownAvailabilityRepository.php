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

    /** @return array<ClownAvailability> */
    public function byMonth(Month $month, bool $indexedByClown = false): array
    {
        return $this->doctrineRepository->createQueryBuilder('ca', $indexedByClown ? 'ca.clown' : null)
            ->leftJoin('ca.clown', 'clown')
            ->where('ca.month = :month')
            ->setParameter('month', $month->getKey())
            ->getQuery()
            ->enableResultCache(1)
            ->getResult();
    }
}
