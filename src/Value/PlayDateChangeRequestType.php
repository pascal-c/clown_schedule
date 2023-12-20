<?php

declare(strict_types=1);

namespace App\Value;

enum PlayDateChangeRequestType: string
{
    case SWAP = 'swap';
    case GIVE_OFF = 'give-off';
}
