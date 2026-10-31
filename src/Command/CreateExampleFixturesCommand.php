<?php

namespace App\Command;

use App\Entity\Month;
use App\Factory\ClownAvailabilityFactory;
use App\Factory\ClownFactory;
use App\Factory\ConfigFactory;
use App\Factory\ContactFactory;
use App\Factory\PlayDateChangeRequestFactory;
use App\Factory\PlayDateFactory;
use App\Factory\VenueFactory;
use App\Factory\FeeFactory;
use App\Factory\InvoiceFactory;
use App\Factory\NotificationFactory;
use App\Factory\RecurringDateFactory;
use App\Factory\ScheduleFactory;
use App\Lib\Collection;
use App\Value\PlayDateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-example-fixtures',
    description: 'populates database with some example data',
)]
class CreateExampleFixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClownFactory $clownFactory,
        private ClownAvailabilityFactory $clownAvailabilityFactory,
        private PlayDateFactory $playDateFactory,
        private PlayDateChangeRequestFactory $_,
        private VenueFactory $venueFactory,
        private FeeFactory $feeFactory,
        private ContactFactory $contactFactory,
        private ScheduleFactory $_scheduleFactory,
        private ConfigFactory $_configFactory,
        private RecurringDateFactory $recurringDateFactory,
        private InvoiceFactory $_invoiceFactory,
        private NotificationFactory $_notificationFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        /*if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }*/

        $this->entityManager->createQuery('DELETE FROM App\Entity\Calendar')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\RecurringDate')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Schedule')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Notification')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Invoice')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\PlayDateHistory')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\PlayDateChangeRequest')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\PlayDate')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Fee')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Contact')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ClownVenuePreference')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Venue')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Substitution')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ClownAvailabilityTime')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ClownAvailability')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Clown')->execute();

        $clowns = $this->clownFactory->createList(8);
        $clowns->push($this->clownFactory->create(email: 'admin-clown@clowns-und-clowns.de', isAdmin: true));
        $clowns->push($this->clownFactory->create(email: 'clown@clowns-und-clowns.de'));

        $venues = Collection::create(
            fn () => $this->venueFactory->create(playingClowns: $clowns->samples(0, 4)),
            10,
        );
        foreach ($venues as $venue) {
            $this->feeFactory->create(venue: $venue);
            $this->contactFactory->createList($venue, 1, 3);

        }

        $month = Month::build('2 months ago');
        $maxCount = 8;
        for ($i = 0; $i < $maxCount; ++$i) {
            foreach ($clowns as $clown) {
                if (rand(1, $maxCount * 3) >= $i) {
                    $this->clownAvailabilityFactory->create($clown, $month);
                }
            }

            // create regular play dates
            Collection::create(
                fn () => $this->playDateFactory->create(month: $month, venue: $venues->sample()),
                25,
            );

            // create special play dates
            Collection::create(
                fn () => $this->playDateFactory->create(month: $month, venue: null, type: PlayDateType::SPECIAL, title: 'Probespiel St. Elisabeth'),
                2,
            );

            // create a training
            $this->playDateFactory->create(month: $month, venue: null, type: PlayDateType::TRAINING, title: 'Training');

            $month = $month->next();
        }

        $this->entityManager->flush();

        $io->success('Yeah! Database was repopopulated with cool faker data!');

        return Command::SUCCESS;
    }
}
