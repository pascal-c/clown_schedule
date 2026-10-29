<?php

namespace App\Command;

use App\Service\Notification\InvoiceEmailReminderNotifier;
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
        private InvoiceEmailReminderNotifier $invoiceEmailReminderNotifier,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $invoicesToNotify = $this->invoiceEmailReminderNotifier->notify();

        $this->entityManager->flush();
        $io->success('All right! I created '.$invoicesToNotify.' invoice reminder notifications.');

        return Command::SUCCESS;
    }
}
