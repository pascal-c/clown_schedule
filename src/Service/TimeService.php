<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

class TimeService
{
    public function today(): DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }

    public function middleOfCurrentMonth(): DateTimeImmutable
    {
        return $this->today()->modify('first day of')->modify('+15 days');
    }
}
