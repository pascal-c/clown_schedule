<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

class TimeService
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }

    public function currentYear(): string
    {
        return $this->today()->format('Y');
    }

    public function NearlyEndOfMonth(): DateTimeImmutable
    {
        return $this->today()->modify('first day of')->modify('+25 days');
    }
}
