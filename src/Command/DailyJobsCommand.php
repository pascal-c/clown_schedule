<?php

namespace App\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:daily-jobs',
    description: 'run all jobs that should be run every day for this application',
    help: 'this command can be run safely several times a day',
)]
class DailyJobsCommand
{
    public function __invoke(Application $application, InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputs = [
            new ArrayInput(['command' => 'app:create-invoices']),
            new ArrayInput(['command' => 'app:close-invalid-change-requests']),
        ];

        foreach ($inputs as $commandInput) {
            $io->writeln('run command '.$commandInput);
            $commandInput->setInteractive(false);
            $application->doRun($commandInput, $output);
        }

        $io->writeln('All right! All jobs ran');

        return Command::SUCCESS;
    }
}
