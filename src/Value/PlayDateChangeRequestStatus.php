<?php

declare(strict_types=1);

namespace App\Value;

enum PlayDateChangeRequestStatus: string
{
    case WAITING = 'waiting';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case CLOSED = 'closed';
}
