<?php

declare(strict_types=1);

namespace App\Value;

use InvalidArgumentException;

enum Preference: string
{
    case WORST = 'worst';
    case WORSE = 'worse';
    case OK = 'ok';
    case BETTER = 'better';
    case BEST = 'best';

    public function short(): string
    {
        return match($this) {
            Preference::WORST => '--',
            Preference::WORSE => '-',
            Preference::OK => 'o',
            Preference::BETTER => '+',
            Preference::BEST => '++',
        };
    }

    public function int(): int
    {
        return match($this) {
            Preference::WORST => 5,
            Preference::WORSE => 4,
            Preference::OK => 3,
            Preference::BETTER => 2,
            Preference::BEST => 1,
        };
    }

    public static function fromInt(int $value): Preference
    {
        return match($value) {
            5 => Preference::WORST,
            4 => Preference::WORSE,
            3 => Preference::OK,
            2 => Preference::BETTER,
            1 => Preference::BEST,
            default => throw new InvalidArgumentException("Invalid preference integer value: $value"),
        };
    }
}
