<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Week;
use App\Service\TimeService;
use Doctrine\ORM\QueryBuilder;

class PlayDateRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    protected function getEntityName(): string
    {
        return PlayDate::class;
    }

    public function find(int $id): PlayDate
    {
        return $this->doctrineRepository->find($id);
    }

    public function minYear(): string
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->select('substring(min(pd.date), 1, 4)')
            ->getQuery()
            ->getResult()[0][1] ?? $this->timeService->currentYear();
    }

    public function maxYear(): string
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->select('substring(max(pd.date), 1, 4)')
            ->getQuery()
            ->getResult()[0][1] ?? $this->timeService->currentYear();
    }

    public function byYear(string $year): array
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->leftJoin('pd.playingClowns', 'clown')
            ->leftJoin('pd.venue', 'venue')
            ->leftJoin('venue.blockedClowns', 'blockedClown')
            ->leftJoin('venue.responsibleClowns', 'responsibleClown')
            ->where('pd.date >= :start')
            ->andWhere('pd.date <= :end')
            ->setParameter('start', "{$year}-01-01")
            ->setParameter('end', "{$year}-12-31")
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function regularByMonth(Month $month): array
    {
        return $this->queryByMonth($month)
            ->andWhere('pd.isSpecial = 0')
            ->getQuery()
            ->enableResultCache(2)
            ->getResult();
    }

    public function byMonth(Month $month): array
    {
        return $this->queryByMonth($month)
            ->getQuery()
            ->enableResultCache(2)
            ->getResult()
        ;
    }

    public function countByClownAvailabilityAndWeek(ClownAvailability $clownAvailability, Week $week): int
    {
        return count(array_filter(
            $this->byMonth($clownAvailability->getMonth()),
            fn ($playDate) => $week == $playDate->getWeek()
                && $playDate->getPlayingClowns()->contains($clownAvailability->getClown())
        ));
    }

    public function byMonthAndClown(Month $month, Clown $clown): array
    {
        return $this->queryByMonth($month)
            ->andWhere('clown = :clown')
            ->setParameter('clown', $clown)
            ->getQuery()
            ->enableResultCache(1)
            ->getResult()
        ;
    }

    private function queryByMonth(Month $month): QueryBuilder
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->leftJoin('pd.playingClowns', 'clown')
            ->leftJoin('pd.venue', 'venue')
            ->leftJoin('venue.blockedClowns', 'blockedClown')
            ->leftJoin('venue.responsibleClowns', 'responsibleClown')
            ->where('pd.date >= :this')
            ->andWhere('pd.date < :next')
            ->setParameter('this', $month->dbFormat())
            ->setParameter('next', $month->next()->dbFormat())
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC');
    }

    public function futureByMonth(Month $month): array
    {
        return $this->doctrineRepository->createQueryBuilder('pd')
            ->where('pd.date >= :min_date')
            ->andWhere('pd.date < :max_date')
            ->setParameter('min_date', max($month->dbFormat(), $this->timeService->today()->format('Y-m-d')))
            ->setParameter('max_date', $month->next()->dbFormat())
            ->orderBy('pd.date', 'ASC')
            ->addOrderBy('pd.daytime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function futureByClown(Clown $clown): array
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
            ->getResult();
    }
}
