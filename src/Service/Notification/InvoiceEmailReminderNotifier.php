<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\Invoice;
use App\Mailer\ClownInfoMailer;
use App\Repository\InvoiceRepository;
use App\Repository\NotificationRepository;
use App\Service\ConfigService;
use App\Service\TimeService;
use App\Value\NotificationType;

class InvoiceEmailReminderNotifier
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private NotificationRepository $notificationRepository,
        private ClownInfoMailer $mailer,
        private ConfigService $configService,
        private NotificationService $notificationService,
        private TimeService $timeService,
    ) {
    }

    public function notify(): int
    {
        $invoices = $this->invoiceRepository->findOpen();
        $invoicesToNotify = array_filter(
            $invoices,
            fn (Invoice $invoice) => !$this->notificationRepository->exists(
                NotificationType::INVOICE_EMAIL_REMINDER,
                $invoice->getClown(),
                $invoice->getMonth(),
            ) && $this->timeService->today() >= $invoice->getMonth()->next()->getDate()->modify('+14 days')
            && $invoice->getClown()->getSettings()->openInvoiceEmailReminder
            && $this->configService->hasFeatureInvoicingClowns()
        );
        foreach ($invoicesToNotify as $invoice) {
            $this->notificationService->create(
                NotificationType::INVOICE_EMAIL_REMINDER,
                $invoice->getClown(),
                $invoice->getMonth(),
            );

            $this->mailer->sendInvoiceNotificationReminder($invoice);
        }

        return count($invoicesToNotify);
    }
}
