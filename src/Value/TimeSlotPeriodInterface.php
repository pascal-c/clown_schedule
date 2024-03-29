<?php

declare(strict_types=1);

namespace App\Value;

use App\Entity\Month;

interface TimeSlotPeriodInterface extends TimeSlotInterface
{
    public const ALL = 'all';
    public const DAYTIMES = [self::ALL, self::AM, self::PM];

    /**
     * @return array <int, TimeSlotInterface>
     */
    public function getTimeSlots(): array;

    public function equalsTimeSlotPeriod(TimeSlotPeriodInterface $other): bool;

    public function getMonth(): Month;
}
