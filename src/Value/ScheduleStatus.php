<?php

declare(strict_types=1);

namespace App\Value;

enum ScheduleStatus: string
{
    case NOT_STARTED = 'not-started';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
}
