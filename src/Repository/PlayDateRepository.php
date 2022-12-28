<?php

namespace App\Repository;

use App\Entity\Month;
use App\Entity\PlayDate;

class PlayDateRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return PlayDate::class;
    }

    public function find(int $id): PlayDate
    {
        return $this->doctrineRepository->find($id);
    }

    public function all(): Array
    {
        return $this->doctrineRepository->findBy(
            [],
            ['date' => 'ASC', 'daytime' => 'ASC']
        );
    }

    public function byMonth(Month $month): Array
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->where('pd.date >= :this')
            ->andWhere('pd.date < :next')
            ->setParameter('this', $month->dbFormat())
            ->setParameter('next', $month->next()->dbFormat())
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC')
            ->getQuery()
            ->enableResultCache()
            ->getResult();
    }
}
