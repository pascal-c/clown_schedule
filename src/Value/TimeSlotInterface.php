<?php

declare(strict_types=1);

namespace App\Value;

use App\Entity\Month;

interface TimeSlotInterface
{
    const AM = 'am';
    const PM = 'pm';
    const DAYTIMES = [self::AM, self::PM];

    public function getDate(): ?\DateTimeImmutable;

    public function getDaytime(): ?string;

    public function getMonth(): Month;
}
