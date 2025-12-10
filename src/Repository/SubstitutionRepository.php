<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Substitution;
use App\Service\ArrayCache;
use App\Service\TimeService;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use DateTimeInterface;

class SubstitutionRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService, private ArrayCache $cache)
    {
    }

    protected function getEntityName(): string
    {
        return Substitution::class;
    }

    public function find(DateTimeInterface $date, string $daytime): ?Substitution
    {
        $this->byMonth(new Month($date));

        return $this->cache->get(
            $this->findCacheKey($date, $daytime),
            fn () => null,
        );
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
        $substitutions = $this->cache->get(
            $this->byMonthCacheKey($month),
            fn (): array => $this->doctrineRepository->createQueryBuilder('sub')
                ->where('sub.month = :month')
                ->setParameter('month', $month->getKey())
                ->getQuery()
                ->getResult(),
        );

        foreach ($substitutions as $substitution) {
            $this->cache->get($this->findCacheKey($substitution->getDate(), $substitution->getDaytime()), fn () => $substitution);
        }

        return $substitutions;
    }

    public function byMonthCacheKey(Month $month): string
    {
        return self::class.'byMonth'.$month->getKey();
    }

    public function findCacheKey(DateTimeInterface $date, string $daytime): string
    {
        return self::class.'find'.$date->format('d').$daytime;
    }

    /** @return array<Substitution> */
    public function byMonthAndClown(Month $month, Clown $clown): array
    {
        $substitutions = $this->doctrineRepository->findBy([
            'month' => $month->getKey(),
            'substitutionClown' => $clown,
        ]);

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

    public function byClown(Clown $clown): array
    {
        return $this->doctrineRepository->createQueryBuilder('ts')
            ->leftJoin('ts.substitutionClown', 'clown')
            ->where('clown = :clown')
            ->setParameter('clown', $clown)
            ->getQuery()
            ->getResult();
    }
}
