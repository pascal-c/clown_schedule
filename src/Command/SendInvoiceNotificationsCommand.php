<?php

namespace App\Command;

use App\Service\Notification\InvoiceEmailNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-invoice-notifications',
    description: '
    ',
)]
class SendInvoiceNotificationsCommand
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoiceEmailNotifier $notifier,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $invoicesToNotify = $this->notifier->notify();

        $this->entityManager->flush();
        $io->success('All right! I created '.$invoicesToNotify.' notifications.');

        return Command::SUCCESS;
    }
}
