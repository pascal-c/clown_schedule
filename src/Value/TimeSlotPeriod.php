<?php

declare(strict_types=1);

namespace App\Value;

use App\Lib\Collection;
use DateTimeImmutable;
use InvalidArgumentException;

class TimeSlotPeriod implements TimeSlotPeriodInterface
{
    use TimeSlotPeriodTrait;

    public static function getDaytimeOptions(): Collection
    {
        return new Collection(static::DAYTIMES);
    }

    public function __construct(private DateTimeImmutable $date, private string $daytime)
    {
        if (!static::getDaytimeOptions()->contains($daytime)) {
            throw new InvalidArgumentException($daytime.' is not a valid daytime');
        }
    }
}
