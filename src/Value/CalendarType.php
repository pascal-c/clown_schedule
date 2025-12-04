<?php

declare(strict_types=1);

namespace App\Value;

enum CalendarType: string
{
    case PERSONAL = 'personal';
    case ALL = 'all';

    public function label(): string
    {
        return match($this) {
            static::PERSONAL => 'Persönlicher Kalender (eigene Termine und Springer)',
            static::ALL => 'Vollständiger Kalender',
        };
    }
}
