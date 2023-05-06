<?php

declare(strict_types=1);

namespace App\Value;

use App\Value\TimeSlotInterface;

interface TimeSlotPeriodInterface extends TimeSlotInterface
{
    const ALL = 'all';
    const DAYTIMES = [self::ALL, self::AM, self::PM];

    public function getTimeSlots(): array;
}
