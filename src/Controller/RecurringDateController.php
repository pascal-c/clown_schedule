<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\RecurringDate;
use App\Form\PlayDate\RecurringDateFormType;
use App\Repository\VenueRepository;
use App\Service\RecurringDateService;
use App\Service\TimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecurringDateController extends AbstractProtectedController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VenueRepository $venueRepository,
        private TimeService $timeService,
        private RecurringDateService $recurringDateService,
    ) {
    }

    #[Route('/recurring_dates/new', name: 'recurring_date_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->adminOnly();

        $recurringDate = new RecurringDate();
        $recurringDate->setStartDate($this->timeService->firstOfNextMonth());
        $recurringDate->setEndDate($this->timeService->endOfYear());
        $recurringDate->setRhythm(RecurringDate::RHYTHM_MONTHLY);
        $venueId = $request->query->get('venue_id');
        if (isset($venueId)) {
            $venue = $this->venueRepository->find(intval($venueId));
            $recurringDate->setVenue($venue);
            $recurringDate->setIsSuper($venue->isSuper());
            $recurringDate->setDaytime($venue->getDaytimeDefault());
            $recurringDate->setMeetingTime($venue->getMeetingTime());
            $recurringDate->setPlayTimeFrom($venue->getPlayTimeFrom());
            $recurringDate->setPlayTimeTo($venue->getPlayTimeTo());
        } else {
            $venue = null;
        }

        $form = $this->createForm(RecurringDateFormType::class, $recurringDate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->recurringDateService->buildPlayDates($recurringDate);
            $this->entityManager->persist($recurringDate);
            $this->entityManager->flush();

            $dates = $recurringDate->getPlayDates()->map(fn (PlayDate $playDate) => $playDate->getDate()->format('d.m.Y'));
            $this->addFlash(
                'success',
                'Wiederkehrender Termin wurde erfolgreich gespeichert. Es wurden '.$recurringDate->getPlayDates()->count().' Spieltermine angelegt: '.
                implode(', ', $dates->toArray()),
            );

            if ($venue) {
                return $this->redirectToRoute('venue_play_date_index', ['id' => $venue->getId()]);
            } else {
                return $this->redirectToRoute('schedule');
            }

        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Termin konnte nicht angelegt werden.');
        }

        return $this->render('recurring_date/new.html.twig', [
            'recurringDate' => $recurringDate,
            'form' => $form,
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'venue']), $response);
    }
}
