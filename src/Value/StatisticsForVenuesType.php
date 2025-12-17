<?php

declare(strict_types=1);

namespace App\Value;

enum StatisticsForVenuesType: string
{
    case BY_TYPE = 'byType';
    case BY_STATUS = 'byStatus';

    public function label(): string
    {
        return match($this) {
            static::BY_TYPE => 'Reguläre und Zusatztermine',
            static::BY_STATUS => 'Abgesagte und verschobene Termine',
        };
    }
}
