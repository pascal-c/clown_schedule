<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Form\PlayDate\AssignClownsFormType;
use App\Form\PlayDate\RegularPlayDateFormType;
use App\Form\PlayDate\SpecialPlayDateFormType;
use App\Form\PlayDate\TrainingFormType;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\VenueRepository;
use App\Service\PlayDateHistoryService;
use App\Service\RecurringDateService;
use App\Service\Scheduler\TrainingAssigner;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use App\Value\PlayDateType;
use App\ViewController\PlayDateViewController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateController extends AbstractProtectedController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        private PlayDateRepository $playDateRepository,
        private ScheduleRepository $scheduleRepository,
        private PlayDateHistoryService $playDateHistoryService,
        private ClownRepository $clownRepository,
        private TimeService $timeService,
        private TranslatorInterface $translator,
        private TrainingAssigner $trainingAssigner,
        private PlayDateViewController $playDateViewController,
        private ConfigRepository $configRepository,
        private RecurringDateService $recurringDateService,
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/play_dates/by_year/{year}', name: 'play_date_index', methods: ['GET'])]
    public function index(?string $year = null): Response
    {
        $year ??= $this->timeService->currentYear();
        $years = range($this->playDateRepository->minYear(), $this->playDateRepository->maxYear());

        return $this->render('play_date/index.html.twig', [
            'play_dates' => $this->playDateRepository->byYear($year),
            'activeYear' => $year,
            'years'      => $years,
        ]);
    }

    #[Route('/play_dates/new', name: 'play_date_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VenueRepository $venueRepository): Response
    {
        $this->adminOnly();

        $playDate = new PlayDate();
        $venueId = $request->query->get('venue_id');
        if (isset($venueId)) {
            $venue = $venueRepository->find(intval($venueId));
            $playDate->setVenue($venue);
            $playDate->setIsSuper($venue->isSuper());
            $playDate->setDaytime($venue->getDaytimeDefault());
        } else {
            $venue = null;
        }

        $type = PlayDateType::from($request->query->get('type', PlayDateType::REGULAR->value));
        $playDate->setType($type);
        $formType = match($type) {
            PlayDateType::REGULAR => RegularPlayDateFormType::class,
            PlayDateType::SPECIAL => SpecialPlayDateFormType::class,
            PlayDateType::TRAINING => TrainingFormType::class,
        };
        $form = $this->createForm($formType, $playDate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($playDate);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans($playDate->getType()->value).' wurde erfolgreich angelegt.');

            return $this->redirectAfterSuccess($playDate, $venue);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Termin konnte nicht angelegt werden.');
        }

        return $this->render('play_date/new.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_dates/{id}', name: 'play_date_show', methods: ['GET'])]
    public function show(PlayDate $playDate, Request $request): Response
    {
        if ($playDate->getPlayingClowns()->contains($this->getCurrentClown())) {
            $trainingForm = $this->createFormBuilder($playDate)
                ->add(
                    'unregister',
                    SubmitType::class,
                    ['label' => 'Vom Training abmelden', 'attr' => ['class' => 'btn-danger']]
                )
                ->setAction($this->generateUrl('training_unregister', ['id' => $playDate->getId()]))
                ->getForm();
        } else {
            $trainingForm = $this->createFormBuilder($playDate)
                ->add(
                    'register',
                    SubmitType::class,
                    ['label' => 'Zum Training anmelden'],
                )
                ->setAction($this->generateUrl('training_register', ['id' => $playDate->getId()]))
                ->getForm();
        }
        $deleteFromUrlParams = array_filter(['id' => $playDate->getId(), 'venue_id' => $request->query->get('venue_id')]);
        $deleteForm = $this->createDeleteForm(
            $this->generateUrl('play_date_delete', $deleteFromUrlParams),
            'Diesen Spieltermin löschen',
        );
        $deleteRecurringForm = $this->createDeleteForm(
            $this->generateUrl('play_date_delete_recurring', $deleteFromUrlParams),
            'Diesen Termin und alle künftigen Wiederholungen löschen',
        );

        return $this->render('play_date/show.html.twig', [
            'playDate' => $this->playDateViewController->getPlayDateViewModel($playDate, $this->getCurrentClown()),
            'trainingForm' => $trainingForm,
            'config' => $this->configRepository->find(),
            'delete_form' => $deleteForm,
            'delete_recurring_form' => $deleteRecurringForm,
            'is_past' => $this->timeService->today() > $playDate->getDate(),
        ]);
    }

    #[Route('/play_dates/{id}/register', name: 'training_register', methods: ['POST'])]
    public function registerPlayingClown(PlayDate $playDate, Request $request): Response
    {
        if (!$this->playDateViewController->mayRegisterForTraining($playDate)) {
            throw $this->createAccessDeniedException('Das ist nicht erlaubt.');
        }

        $trainingForm = $this->createFormBuilder($playDate)
            ->add('register', SubmitType::class)
            ->getForm();
        $trainingForm->handleRequest($request);
        if ($trainingForm->isSubmitted() && $trainingForm->isValid()) {
            $this->trainingAssigner->assignOne($playDate, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->addFlash('success', 'Du bist jetzt für das Training angemeldet. Schön, dass Du dabei bist!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } else {
            $this->addFlash('danger', 'Da ist was schiefgegangen, tut mir leid!');
        }

        return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
    }

    #[Route('/play_dates/{id}/unregister', name: 'training_unregister', methods: ['POST'])]
    public function unregisterPlayingClown(PlayDate $playDate, Request $request): Response
    {
        if (!$this->playDateViewController->mayRegisterForTraining($playDate)) {
            throw $this->createAccessDeniedException('Das ist nicht erlaubt.');
        }

        $trainingForm = $this->createFormBuilder($playDate)
            ->add('unregister', SubmitType::class)
            ->getForm();
        $trainingForm->handleRequest($request);
        if ($trainingForm->isSubmitted() && $trainingForm->isValid()) {
            $this->trainingAssigner->unassignOne($playDate, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->addFlash('success', 'Du bist jetzt für das Training abgemeldet. Schade, dass Du nicht dabei sein kannst!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } else {
            $this->addFlash('danger', 'Da ist was schiefgegangen, tut mir leid!');
        }

        return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
    }

    #[Route('/play_dates/{id}/edit', name: 'play_date_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);

        $editFormType = match($playDate->getType()) {
            PlayDateType::REGULAR => RegularPlayDateFormType::class,
            PlayDateType::SPECIAL => SpecialPlayDateFormType::class,
            PlayDateType::TRAINING => TrainingFormType::class,
        };
        $editForm = $this->createForm($editFormType, $playDate, ['method' => 'PUT']);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans($playDate->getType()->value).' wurde aktualisiert. Sehr gut!');

            return $this->redirectAfterSuccess($playDate, $request->query->get('venue_id') ? $playDate->getVenue() : null);
        } elseif ($editForm->isSubmitted()) {
            $this->addFlash('warning', 'Hach! Termin konnte irgendwie nicht aktualisiert werden.');
        }

        return $this->render('play_date/edit.html.twig', [
            'playDate' => $playDate,
            'form' => $editForm,
        ]);
    }

    #[Route('/play_dates/{id}/assign_clowns', name: 'play_date_assign_clowns', methods: ['GET', 'PUT'])]
    public function assignClowns(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);

        $form = $this->createForm(AssignClownsFormType::class, $playDate, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $schedule = $this->scheduleRepository->find($playDate->getMonth());
            $changeReason = !is_null($schedule) && $schedule->isCompleted()
                ? PlayDateChangeReason::MANUAL_CHANGE
                : PlayDateChangeReason::MANUAL_CHANGE_FOR_SCHEDULE;
            $this->playDateHistoryService->add($playDate, $this->getCurrentClown(), $changeReason);
            $this->entityManager->flush();

            $this->addFlash('success', 'Clowns wurden zugeordnet. Tip top!');

            return $this->redirectToRoute('schedule');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Mist, das hat nicht geklappt!');
        }

        return $this->render('play_date/assign_clowns.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_dates/{id}', name: 'play_date_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);

        $deleteForm = $this->createDeleteForm()->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->entityManager->remove($playDate);
            $this->entityManager->flush();

            $this->addFlash('success', 'Spieltermin wurde gelöscht. Das ist gut!');

            if ($request->query->get('venue_id')) {
                return $this->redirectToRoute('venue_play_date_index', ['id' => $playDate->getVenue()->getId()]);
            }

            return $this->redirectToRoute('schedule');
        }

        $this->addFlash('warning', 'Achtung! Spieltermin konnte nicht gelöscht werden.');

        return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
    }

    #[Route('/play_dates/{id}/recurring', name: 'play_date_delete_recurring', methods: ['DELETE'])]
    public function deleteRecurring(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);

        $deleteForm = $this->createDeleteForm()->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $deletedCount = $this->recurringDateService->deletePlayDatesSince($playDate->getRecurringDate(), $playDate->getDate());

            $this->addFlash('success', 'Es wurden '.$deletedCount.' Spieltermine gelöscht. Gut gemacht!');

            if ($request->query->get('venue_id')) {
                return $this->redirectToRoute('venue_play_date_index', ['id' => $playDate->getVenue()->getId()]);
            }

            return $this->redirectToRoute('schedule');
        }

        $this->addFlash('warning', 'Achtung! Spieltermine konnten nicht gelöscht werden.');

        return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
    }

    private function redirectAfterSuccess(PlayDate $playDate, ?Venue $venue)
    {
        if ($playDate->isSpecial() && !$playDate->getPlayDateFee()) {
            return $this->redirectToRoute('play_date_fee_edit', ['id' => $playDate->getId()]);
        } elseif (isset($venue)) {
            return $this->redirectToRoute('venue_play_date_index', ['id' => $venue->getId()]);
        }

        return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
