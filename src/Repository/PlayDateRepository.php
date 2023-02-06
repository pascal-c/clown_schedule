<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Service\TimeService;
use Doctrine\ORM\QueryBuilder;

class PlayDateRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService) {}

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

    public function countRegularTimeSlotsPerMonth(Month $month): int
    {
        $results = $this->doctrineRepository->createQueryBuilder('pd')
            ->select('count(pd.date)')
            ->groupBy('pd.date', 'pd.daytime')
            ->where('pd.date >= :this')
            ->andWhere('pd.date < :next')
            ->andWhere('pd.isSpecial = 0')
            ->setParameter('this', $month->dbFormat())
            ->setParameter('next', $month->next()->dbFormat())
            ->getQuery()
            ->getResult();

        return count($results);
    }

    public function regularByMonth(Month $month): Array
    {
        return $this->queryByMonth($month)
            ->andWhere('pd.isSpecial = 0')
            ->getQuery()
            ->getResult();
    }

    public function byMonth(Month $month): Array
    {
        return $this->queryByMonth($month)
            ->getQuery()
            ->enableResultCache(1)
            ->getResult()
            ;
    }

    private function queryByMonth(Month $month): QueryBuilder
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->where('pd.date >= :this')
            ->andWhere('pd.date < :next')
            ->setParameter('this', $month->dbFormat())
            ->setParameter('next', $month->next()->dbFormat())
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC');
    }

    public function futureByClown(Clown $clown): Array
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->leftJoin('pd.playingClowns', 'clown')
            ->where('pd.date >= :today')
            ->andWhere('clown = :clown')
            ->setParameter('today', $this->timeService->today())
            ->setParameter('clown', $clown)
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC')
            ->getQuery()
            #->enableResultCache()
            ->getResult();
    }
}
