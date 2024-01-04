<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Substitution;
use App\Service\TimeService;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use DateTimeInterface;

class SubstitutionRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    private array $cache = [];

    private function cacheWarmUp(Month $month, array $substitutions): void
    {
        $this->cache[$month->getKey()] = [];
        foreach ($substitutions as $substitution) {
            $this->cache[$month->getKey()][$substitution->getDate()->format('d')][$substitution->getDaytime()] = $substitution;
        }
    }

    private function cacheNeedsWarmUp(Month $month): bool
    {
        return empty($this->cache[$month->getKey()]);
    }

    private function cacheGet(DateTimeInterface $date, string $daytime): ?Substitution
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
        return Substitution::class;
    }

    public function find(DateTimeInterface $date, string $daytime): ?Substitution
    {
        return $this->cacheGet($date, $daytime);
    }

    /**
     * @return array<Substitution>
     */
    public function findByTimeSlotPeriod(TimeSlotPeriodInterface $timeSlotPeriod): array
    {
        return array_filter(array_map(
            fn (TimeSlotInterface $timeSlot) => $this->find($timeSlot->getDate(), $timeSlot->getDaytime()),
            $timeSlotPeriod->getTimeSlots(),
        ));
    }

    /** @return array<Substitution> */
    public function byMonth(Month $month): array
    {
        $substitutions = $this->doctrineRepository->findBy(['month' => $month->getKey()]);
        $this->cacheWarmUp($month, $substitutions);

        return $substitutions;
    }

    public function futureByClown(Clown $clown): array
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
            ->getResult();
    }
}
