<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\TimeSlot;
use App\Service\TimeService;

class TimeSlotRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService) {}

    private array $cache = [];

    private function cacheWarmUp(Month $month, array $timeSlots): void
    {
        $this->cache[$month->getKey()] = [];
        foreach ($timeSlots as $timeSlot) {
            $this->cache[$month->getKey()][$timeSlot->getDate()->format('d')][$timeSlot->getDaytime()] = $timeSlot;
        }
    }

    private function cacheNeedsWarmUp(Month $month): bool
    {
        return empty($this->cache[$month->getKey()]);
    }

    private function cacheGet(\DateTimeInterface $date, string $daytime): ?TimeSlot
    {
        $month = new Month($date);
        if ($this->cacheNeedsWarmUp($month)) {
            $this->cacheWarmUp($month, $this->byMonth($month));
        }

        if (!isset($this->cache[$month->getKey()][$date->format('d')][$daytime])) {
            return null;
        }
        return $this->cache[$month->getKey()][$date->format('d')][$daytime];
    }

    protected function getEntityName(): string
    {
        return TimeSlot::class;
    }

    public function find(\DateTimeInterface $date, string $daytime): ?TimeSlot
    {
        return $this->cacheGet($date, $daytime);
    }

    public function byMonth(Month $month): array
    {
        $timeSlots = $this->doctrineRepository->findBy(['month' => $month->getKey()]);
        $this->cacheWarmUp($month, $timeSlots);
        return $timeSlots;
    }

    public function futureByClown(Clown $clown): Array
    {
        return $this->doctrineRepository->createQueryBuilder('ts')
            ->leftJoin('ts.substitutionClown', 'clown')
            ->where('ts.date >= :today')
            ->andWhere('clown = :clown')
            ->setParameter('today', $this->timeService->today())
            ->setParameter('clown', $clown)
            ->orderBy('ts.date', 'ASC')
            ->addOrderBy('ts.daytime', 'ASC')
            ->getQuery()
            #->enableResultCache()
            ->getResult();
    }
}
