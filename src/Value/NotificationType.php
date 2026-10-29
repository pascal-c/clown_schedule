<?php

declare(strict_types=1);

namespace App\Value;

enum NotificationType: string
{
    case INVOICE_EMAIL = 'invoice';
    case INVOICE_EMAIL_REMINDER = 'inovice_reminder';
}
