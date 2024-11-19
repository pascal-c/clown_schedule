<?php

namespace App\Command;

use App\Service\PlayDateChangeRequestCloseInvalidService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:close-invalid-change-requests',
    description: 'close all invalid play date change requests',
)]
class CloseInvalidChangeRequestsCommand extends Command
{
    public function __construct(
        private PlayDateChangeRequestCloseInvalidService $service,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->service->closeAllInvalidChangeRequests();
        $this->entityManager->flush();

        $io->success('All right! I closed all this damned invalid change requests. Good work!');

        return Command::SUCCESS;
    }
}
