<?php

declare(strict_types=1);

namespace App\Service;

class TimeService
{
    public function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }

    public function NearlyEndOfMonth(): \DateTimeImmutable
    {
        return $this->today()->modify('first day of')->modify('+25 days');
    }

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
