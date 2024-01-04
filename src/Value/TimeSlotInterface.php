<?php

declare(strict_types=1);

namespace App\Value;

use App\Entity\Month;
use DateTimeImmutable;

interface TimeSlotInterface
{
    public const AM = 'am';
    public const PM = 'pm';
    public const DAYTIMES = [self::AM, self::PM];

    public function getDate(): ?DateTimeImmutable;

    public function getDaytime(): ?string;

    public function getMonth(): Month;
}
