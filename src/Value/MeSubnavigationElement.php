<?php

declare(strict_types=1);

namespace App\Value;

enum MeSubnavigationElement: string
{
    case PERSONAL_DATA = 'personal-data';
    case NOTIFICATIONS = 'notifications';
}
