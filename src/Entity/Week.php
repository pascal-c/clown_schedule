<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

class Week
{
    private DateTimeImmutable $date;

    public function __construct(DateTimeImmutable $date)
    {
        $this->date = $date->modify('tomorrow')->modify('last monday');
    }

    public function getId(): string
    {
        return $this->date->format('o-W');
    }
}
