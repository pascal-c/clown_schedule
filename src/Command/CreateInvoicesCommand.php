<?php

namespace App\Command;

use App\Repository\MonthRepository;
use App\Service\InvoiceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-invoices',
    description: 'create invoices for confirmed and paid play dates',
)]
class CreateInvoicesCommand extends Command
{
    public function __construct(
        private InvoiceService $service,
        private MonthRepository $monthRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $month = $this->monthRepository->current()->previous();
        $this->service->createInvoices($month);
        $this->entityManager->flush();

        $io->success('All right! I created invoices for all confirmed and paid play dates for Month '.$month->getKey().'. Nice!');

        return Command::SUCCESS;
    }
}
