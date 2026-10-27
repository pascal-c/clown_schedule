<?php

namespace App\Command;

use App\Entity\Invoice;
use App\Mailer\ClownInfoMailer;
use App\Repository\InvoiceRepository;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use App\Service\TimeService;
use App\Value\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-invoice-notifications-reminder',
    description: '
    ',
)]
class SendInvoiceNotificationsReminderCommand
{
    public function __construct(
        private ClownInfoMailer $mailer,
        private TimeService $timeService,
        private InvoiceRepository $invoiceRepository,
        private NotificationRepository $notificationRepository,
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $invoices = $this->invoiceRepository->findOpen();
        $invoicesToNotify = array_filter(
            $invoices,
            fn (Invoice $invoice) => !$this->notificationRepository->exists(
                NotificationType::INVOICE_REMINDER,
                $invoice->getClown(),
                $invoice->getMonth(),
            ) && $this->timeService->today() >= $invoice->getMonth()->next()->getDate()->modify('+14 days')
        );
        foreach ($invoicesToNotify as $invoice) {
            $this->notificationService->addNotification(
                NotificationType::INVOICE_REMINDER,
                $invoice->getClown(),
                $invoice->getMonth(),
            );

            $this->mailer->sendInvoiceNotificationReminder($invoice);
        }

        $this->entityManager->flush();
        $io->success('All right! I created '.count($invoicesToNotify).' invoice reminder notifications.');

        return Command::SUCCESS;
    }
}
