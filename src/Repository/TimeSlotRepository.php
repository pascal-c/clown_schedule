<?php

namespace App\Repository;

use App\Entity\Month;
use App\Entity\TimeSlot;

class TimeSlotRepository extends AbstractRepository
{
    private array $cache = [];

    private function cacheWarmUp(Month $month): void
    {
        $timeSlots = $this->byMonth($month);
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
            $this->cacheWarmUp($month);
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

    private function byMonth(Month $month): array
    {
        return $this->doctrineRepository->findBy(['month' => $month->getKey()]);
    }
}
