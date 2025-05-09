<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Form\PlayDate\AssignClownsFormType;
use App\Form\PlayDate\RegularPlayDateFormType;
use App\Form\PlayDate\SpecialPlayDateFormType;
use App\Form\PlayDate\TrainingFormType;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Repository\VenueRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateHistoryService;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use App\Value\PlayDateType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateController extends AbstractController
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
    public function show(SubstitutionRepository $substitutionRepository, ConfigRepository $configRepository, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);
        if (is_null($playDate)) {
            throw new NotFoundHttpException();
        }

        return $this->render('play_date/show.html.twig', [
            'playDate' => $playDate,
            'substitutionClowns' => array_map(
                fn (Substitution $substitution) => $substitution->getSubstitutionClown(),
                $substitutionRepository->findByTimeSlotPeriod($playDate),
            ),
            'specialPlayDateUrl' => $playDate->isSpecial() ? $configRepository->find()->getSpecialPlayDateUrl() : '',
            'showChangeRequestLink' => $playDate->getPlayingClowns()->contains($this->getCurrentClown()) && $playDate->getDate() >= $this->timeService->today()->modify(PlayDateChangeRequestCloseInvalidService::CREATABLE_UNTIL_PERIOD),
        ]);
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
        $deleteForm = $this->createFormBuilder($playDate)
            ->add(
                'delete',
                SubmitType::class,
                ['label' => 'Spieltermin löschen', 'attr' => ['onclick' => 'return confirm("Spieltermin endgültig löschen?")']]
            )
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('play_date_delete', ['id' => $id]))
            ->getForm();

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
            'delete_form' => $deleteForm,
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

        $deleteForm = $this->createFormBuilder($playDate)
            ->add('delete', SubmitType::class, ['label' => 'Spieltermin löschen'])
            ->setMethod('DELETE')
            ->getForm();
        $deleteForm->handleRequest($request);

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

        return $this->redirectToRoute('play_date_edit', ['id' => $playDate->getId()]);
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
