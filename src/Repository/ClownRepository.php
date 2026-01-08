<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Service\TimeService;
use App\Value\PlayDateType;
use Doctrine\ORM\EntityRepository;

class ClownRepository extends AbstractRepository
{
    protected EntityRepository $doctrineRepository;

    public function __construct(private TimeService $timeService)
    {
    }

    protected function getEntityName(): string
    {
        return Clown::class;
    }

    public function all(): array
    {
        return $this->doctrineRepository->findBy(
            [],
            ['isActive' => 'DESC', 'name' => 'ASC']
        );
    }

    public function allActive(): array
    {
        return $this->doctrineRepository->findBy(
            ['isActive' => true],
            ['name' => 'ASC']
        );
    }

    public function find(int $id): ?Clown
    {
        return $this->doctrineRepository->find($id);
    }

    public function findOneByEmail(string $email): ?Clown
    {
        return $this->doctrineRepository->findOneByEmail($email);
    }

    public function allWithConfirmedPlayDateCounts(?string $year = null): array
    {
        $queryBuilder = $this->doctrineRepository->createQueryBuilder('cl')
            ->select('cl AS clown, count(DISTINCT(pd.id)) AS totalCount')
            ->leftJoin('cl.playDates', 'pd')
            ->where('pd.type = :play_date_type')
            ->andWhere('pd.status = :status_confirmed')
            ->andWhere('pd.date < :firstOfNextMonth')
            ->setParameter('firstOfNextMonth', $this->timeService->firstOfNextMonth())
            ->setParameter('play_date_type', PlayDateType::REGULAR->value)
            ->setParameter('status_confirmed', PlayDate::STATUS_CONFIRMED)
            ->groupBy('cl')
            ->orderBy('cl.isActive', 'DESC')
            ->addOrderBy('cl.name', 'ASC');
        if ($year) {
            $queryBuilder
                ->andWhere('pd.date >= :startDate')
                ->andWhere('pd.date <= :endDate')
                ->setParameter('startDate', "{$year}-01-01")
                ->setParameter('endDate', "{$year}-12-31");
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function allWithConfirmedSuperPlayDateCounts(?string $year = null): array
    {
        $queryBuilder = $this->doctrineRepository->createQueryBuilder('cl')
            ->select('cl AS clown, count(pd.id) AS superCount')
            ->leftJoin('cl.playDates', 'pd')
            ->where('pd.isSuper = 1')
            ->andWhere('pd.status = :status_confirmed')
            ->andWhere('pd.date < :firstOfNextMonth')
            ->setParameter('firstOfNextMonth', $this->timeService->firstOfNextMonth())
            ->setParameter('status_confirmed', PlayDate::STATUS_CONFIRMED)
            ->groupBy('cl');
        if ($year) {
            $queryBuilder
                ->andWhere('pd.date >= :start')
                ->andWhere('pd.date <= :end')
                ->setParameter('start', "{$year}-01-01")
                ->setParameter('end', "{$year}-12-31");
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
