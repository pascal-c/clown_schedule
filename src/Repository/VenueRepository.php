<?php

namespace App\Repository;

use App\Entity\PlayDate;
use App\Entity\Venue;

class VenueRepository extends AbstractRepository
{
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

    public function allWithConfirmedPlayDates(?string $year = null): array
    {
        $queryBuilder = $this->doctrineRepository->createQueryBuilder('venue')
            ->select('venue, pd')
            ->leftJoin('venue.playDates', 'pd')
            ->where('pd.status = :status_confirmed')
            ->setParameter('status_confirmed', PlayDate::STATUS_CONFIRMED);

        if ($year) {
            $queryBuilder
                ->andWhere('pd.date >= :start')
                ->andWhere('pd.date <= :end')
                ->setParameter('start', "{$year}-01-01")
                ->setParameter('end', "{$year}-12-31");
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
