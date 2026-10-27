<?php

declare(strict_types=1);

namespace App\Value;

enum NotificationType: string
{
    case INVOICE = 'invoice';
    case INVOICE_REMINDER = 'inovice_reminder';
}
