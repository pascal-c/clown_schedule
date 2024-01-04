<?php

namespace App\Entity;

use DateTimeImmutable;

class Vacation
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        private string $name
    ) {
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->end;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
