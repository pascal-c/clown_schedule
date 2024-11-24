<?php

declare(strict_types=1);

namespace App\Value;

enum PlayDateType: string
{
    case REGULAR = 'regular';
    case SPECIAL = 'special';
    case TRAINING = 'training';

    public function isRegular(): bool
    {
        return PlayDateType::REGULAR === $this;
    }
}
