<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Month;
use DateTimeImmutable;

class TimeService
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function today(): DateTimeImmutable
    {
        return $this->now()->modify('today');
    }

    public function currentYear(): string
    {
        return $this->today()->format('Y');
    }

    public function firstOfMonth(): DateTimeImmutable
    {
        return $this->today()->modify('first day of');
    }

    public function NearlyEndOfMonth(): DateTimeImmutable
    {
        return $this->firstOfMonth()->modify('+25 days');
    }

    public function firstOfNextMonth(): DateTimeImmutable
    {
        return $this->today()->modify('+1 month first day of');
    }

    public function endOfYear(): DateTimeImmutable
    {
        return $this->today()->modify('last day of December');
    }

    public function nThWeekdayOfMonth(int $n, string $weekday, Month $month): ?DateTimeImmutable
    {
        $result = $month->getDate()->modify($n.' '.$weekday);
        if ($result >= $month->next()->getDate()) {
            return null;
        }

        return $result;
    }
}
