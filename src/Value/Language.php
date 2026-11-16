<?php

declare(strict_types=1);

namespace App\Value;

enum Language: string
{
    case DE = 'de';
    case EN = 'en';

    public function label(): string
    {
        return match($this) {
            static::DE => 'deutsch',
            static::EN => 'english',
        };
    }
}
