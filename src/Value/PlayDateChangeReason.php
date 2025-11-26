<?php

declare(strict_types=1);

namespace App\Value;

enum PlayDateChangeReason: string
{
    case CALCULATION = 'calculation';
    case CANCEL = 'cancel';
    case CREATE = 'create';
    case GIVE_OFF = 'give-off';
    case MANUAL_CHANGE_FOR_SCHEDULE = 'manual-change-for-schedule';
    case MANUAL_CHANGE = 'manual-change';
    case MOVE = 'move';
    case SWAP = 'swap';
}
