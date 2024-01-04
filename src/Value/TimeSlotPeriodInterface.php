<?php

declare(strict_types=1);

namespace App\Value;

interface TimeSlotPeriodInterface extends TimeSlotInterface
{
    public const ALL = 'all';
    public const DAYTIMES = [self::ALL, self::AM, self::PM];

    /**
     * @return array <int, TimeSlotInterface>
     */
    public function getTimeSlots(): array;
}
