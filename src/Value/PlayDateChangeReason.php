<?php

declare(strict_types=1);

namespace App\Value;

enum PlayDateChangeReason: string
{
    case CALCULATION = 'calculation';
    case MANUAL_CHANGE_FOR_SCHEDULE = 'manual-change-for-schedule';
    case MANUAL_CHANGE = 'manual-change';
    case SWAP = 'swap';
}
