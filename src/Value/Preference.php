<?php

declare(strict_types=1);

namespace App\Value;

enum Preference: string
{
    case WORST = 'worst';
    case WORSE = 'worse';
    case OK = 'ok';
    case BETTER = 'better';
    case BEST = 'best';
}
