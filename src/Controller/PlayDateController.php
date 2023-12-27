<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Form\PlayDateAssignClownsFormType;
use App\Form\PlayDateFormType;
use App\Form\SpecialPlayDateFormType;
use App\Repository\ClownRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Repository\VenueRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateHistoryService;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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
    )
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/play_dates', name: 'play_date_index', methods: ['GET'])]
    public function index(): Response 
    {
        return $this->render('play_date/index.html.twig', [
            'play_dates' => $this->playDateRepository->all(),
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
        }
        else {
            $venue = null;
        }

        $isSpecial = $request->query->get('isSpecial');
        $form = $this->createForm($isSpecial ? SpecialPlayDateFormType::class : PlayDateFormType::class, $playDate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $playDate = $form->getData();

            $this->entityManager->persist($playDate);
            $this->entityManager->flush();

            $this->addFlash('success', 'Spieltermin wurde erfolgreich angelegt.');
            return $this->redirectAfterSuccess($venue);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Spieltermin konnte nicht angelegt werden.');
        }

        return $this->render('play_date/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/play_dates/{id}', name: 'play_date_show', methods: ['GET'])]
    public function show(SubstitutionRepository $substitutionRepository, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);
        if (is_null($playDate)) {
            throw(new NotFoundHttpException);  
        }

        return $this->render('play_date/show.html.twig', [
            'playDate' => $playDate,
            'substitutionClowns' => array_map(
                fn(Substitution $substitution) => $substitution->getSubstitutionClown(),
                $substitutionRepository->findByTimeSlotPeriod($playDate),
            ),
            'showChangeRequestLink' => $playDate->getPlayingClowns()->contains($this->getCurrentClown()) && $playDate->getDate() >= $this->timeService->today()->modify(PlayDateChangeRequestCloseInvalidService::CREATABLE_UNTIL_PERIOD),
        ]);
    }

    #[Route('/play_dates/edit/{id}', name: 'play_date_edit', methods: ['GET', 'PATCH'])]
    public function edit(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);

        $editForm = $this->createForm($playDate->isSpecial() ? SpecialPlayDateFormType::class : PlayDateFormType::class, $playDate, ['method' => 'PATCH']);
        $deleteForm = $this->createFormBuilder($playDate)
            ->add('delete', SubmitType::class, 
                ['label' => 'Spieltermin löschen', 'attr' => array('onclick' => 'return confirm("Spieltermin endgültig löschen?")')])
            ->setMethod('DELETE')
            ->getForm();

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $playDate = $editForm->getData();
            $playDate->setIsSuper($editForm['isSuper']->isSubmitted());
            $this->entityManager->flush();

            $this->addFlash('success', 'Spieltermin wurde aktualisiert. Sehr gut!');
            return $this->redirectAfterSuccess($request->query->get('venue_id') ? $playDate->getVenue() : null);
        } elseif ($editForm->isSubmitted()) {
            $this->addFlash('warning', 'Hach! Spieltermin konnte irgendwie nicht aktualisiert werden.');
        }

        return $this->render('play_date/edit.html.twig', [
            'form' => $editForm,
            'delete_form' => $deleteForm,
        ]);
    }

    #[Route('/play_dates/{id}/assign_clowns', name: 'play_date_assign_clowns', methods: ['GET', 'PATCH'])]
    public function assignClowns(Request $request, int $id): Response
    {
        $this->adminOnly();

        $playDate = $this->playDateRepository->find($id);
        
        $form = $this->createForm(PlayDateAssignClownsFormType::class, $playDate, ['method' => 'PATCH']);
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
            return $this->redirectAfterSuccess($request->query->get('venue_id') ? $playDate->getVenue() : null);
        }

        $this->addFlash('warning', 'Achtung! Spieltermin konnte nicht gelöscht werden.');
        return $this->redirectToRoute('play_date_edit', ['id' => $playDate->getId()]);
    }

    private function redirectAfterSuccess(?Venue $venue)
    {
        if (isset($venue)) {
            return $this->redirectToRoute('venue_show', ['id' => $venue->getId()]);
        }
        return $this->redirectToRoute('schedule');
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
