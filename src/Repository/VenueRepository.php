<?php

namespace App\Repository;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Service\TimeService;
use App\Value\PlayDateType;

class VenueRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    protected function getEntityName(): string
    {
        return Venue::class;
    }

    public function find(int $id): Venue
    {
        return $this->doctrineRepository->find($id);
    }

    public function active(): array
    {
        return $this->doctrineRepository->findBy(
            ['archived' => false],
            ['name' => 'ASC']
        );
    }

    public function archived(): array
    {
        return $this->doctrineRepository->findBy(
            ['archived' => true],
            ['name' => 'ASC']
        );
    }

    /**
     * @return Venue[]
     */
    public function allWithPlays(?string $year = null, bool $onlyConfirmed = false): array
    {
        $queryBuilder = $this->doctrineRepository->createQueryBuilder('venue')
            ->select('venue, pd')
            ->where('pd.type = :type_regular OR pd.type = :type_special')
            ->andWhere('pd.date < :firstOfNextMonth')
            ->setParameter('firstOfNextMonth', $this->timeService->firstOfNextMonth())
            ->setParameter('type_regular', PlayDateType::REGULAR->value)
            ->setParameter('type_special', PlayDateType::SPECIAL->value)
            ->leftJoin('venue.playDates', 'pd');

        if ($year) {
            $queryBuilder
                ->andWhere('pd.date >= :start')
                ->andWhere('pd.date <= :end')
                ->setParameter('start', "{$year}-01-01")
                ->setParameter('end', "{$year}-12-31");
        }

        if ($onlyConfirmed) {
            $queryBuilder
                ->andWhere('pd.status = :status_confirmed_only')
                ->setParameter('status_confirmed_only', PlayDate::STATUS_CONFIRMED);
        }

        return $queryBuilder
            ->orderBy('venue.archived', 'ASC')
            ->addOrderBy('venue.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function all(): array
    {
        return $this->doctrineRepository->findAll();
    }
}
