<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueFormType;
use App\Repository\ConfigRepository;
use App\Repository\VenueRepository;
use App\Service\TimeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VenueController extends AbstractProtectedController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        private VenueRepository $venueRepository,
        private TimeService $timeService,
        private ConfigRepository $configRepository,
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/venues', name: 'venue_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status', default: 'active');

        return $this->render('venue/index.html.twig', [
            'venues' => ('archived' === $status) ? $this->venueRepository->archived() : $this->venueRepository->active(),
            'active' => 'venue',
            'status' => $status,
            'showBlockedClowns' => $this->configRepository->isFeatureCalculationActive(),
            'showResponsibleClowns' => $this->configRepository->isFeatureCalculationActive() && $this->configRepository->isFeatureAssignResponsibleClownAsFirstClownActive(),
        ]);
    }

    #[Route('/venues/new', name: 'venue_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->adminOnly();

        $venue = new Venue();
        $config = $this->configRepository->find();
        $venue->setAssignResponsibleClownAsFirstClown($config->useCalculation() && $config->isFeatureAssignResponsibleClownAsFirstClownActive());

        $form = $this->createForm(VenueFormType::class, $venue);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $venue = $form->getData();

            $this->entityManager->persist($venue);
            $this->entityManager->flush();

            $this->addFlash('success', 'Spielort wurde angelegt. Phantastisch!');

            return $this->redirectToRoute('venue_show', ['id' => $venue->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Spielort konnte nicht angelegt werden.');
        }

        return $this->render('venue/new.html.twig', [
            'form' => $form,
            'active' => 'venue',
        ]);
    }

    #[Route('/venues/edit/{id}', name: 'venue_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, int $id): Response
    {
        $this->adminOnly();

        $venue = $this->venueRepository->find($id);

        $editForm = $this->createForm(VenueFormType::class, $venue, ['method' => 'PUT']);
        $deleteForm = $this->createFormBuilder($venue)
            ->add(
                'delete',
                SubmitType::class,
                ['label' => 'Spielort löschen', 'attr' => ['onclick' => 'return confirm("Spielort endgültig löschen?")']]
            )
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('venue_delete', ['id' => $id]))
            ->getForm();
        $archiveForm = $this->getArchiveForm($venue);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Spielort wurde aktualisiert. Super!');

            return $this->redirectToRoute('venue_show', ['id' => $id]);
        } elseif ($editForm->isSubmitted()) {
            $this->addFlash('warning', 'Oh! Spielort konnte nicht aktualisiert werden.');
        }

        return $this->render('venue/edit.html.twig', [
            'venue' => $venue,
            'form' => $editForm,
            'delete_form' => $deleteForm,
            'archive_form' => $archiveForm,
            'active' => 'venue',
        ]);
    }

    #[Route('/venues/archive/{id}', name: 'venue_archive', methods: ['DELETE'])]
    public function archive(Request $request, int $id): Response
    {
        $this->adminOnly();

        $venue = $this->venueRepository->find($id);

        $archiveForm = $this->getArchiveForm($venue);
        $archiveForm->handleRequest($request);

        if ($archiveForm->isSubmitted() && $archiveForm->isValid()) {
            $venue->setArchived(true);
            $this->entityManager->flush();

            $this->addFlash('success', 'Ok! Spielort '.$venue->getName().' wurde archiviert. Du kannst ihn jederzeit wiederherstellen!');

            return $this->redirectToRoute('venue_index');
        }

        $this->addFlash('warning', 'Achtung! Spielort konnte nicht archiviert werden.');

        return $this->redirectToRoute('venue_edit', ['id' => $venue->getId()]);
    }

    #[Route('/venues/restore/{id}', name: 'venue_restore', methods: ['POST'])]
    public function restore(Request $request, int $id): Response
    {
        $this->adminOnly();

        $venue = $this->venueRepository->find($id);

        $archiveForm = $this->getArchiveForm($venue);
        $archiveForm->handleRequest($request);

        if ($archiveForm->isSubmitted() && $archiveForm->isValid()) {
            $venue->setArchived(false);
            $this->entityManager->flush();

            $this->addFlash('success', 'Super! '.$venue->getName().' ist wieder da!');

            return $this->redirectToRoute('venue_index');
        }

        $this->addFlash('warning', 'Achtung! Spielort konnte nicht wiederhergestellt werden.');

        return $this->redirectToRoute('venue_edit', ['id' => $venue->getId()]);
    }

    #[Route('/venues/{id}', name: 'venue_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        $this->adminOnly();

        $venue = $this->venueRepository->find($id);

        $deleteForm = $this->createFormBuilder($venue)
            ->add('delete', SubmitType::class, ['label' => 'Spielort löschen'])
            ->setMethod('DELETE')
            ->getForm();
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->entityManager->remove($venue);
            $this->entityManager->flush();

            $this->addFlash('success', 'Spielort '.$venue->getName().' wurde gelöscht. Danke fürs Aufräumen!');

            return $this->redirectToRoute('venue_index');
        }

        $this->addFlash('warning', 'Achtung! Spielort konnte nicht gelöscht werden.');

        return $this->redirectToRoute('venue_edit', ['id' => $venue->getId()]);
    }

    #[Route('/venues/{id}', name: 'venue_show', methods: ['GET'])]
    public function show(Venue $venue): Response
    {
        return $this->render('venue/show.html.twig', [
            'venue' => $venue,
            'active' => 'venue',
            'showBlockedClowns' => $this->configRepository->isFeatureCalculationActive(),
            'showResponsibleClowns' => $this->configRepository->isFeatureCalculationActive() && $this->configRepository->isFeatureAssignResponsibleClownAsFirstClownActive() && $venue->assignResponsibleClownAsFirstClown(),
        ]);
    }

    private function getArchiveForm(Venue $venue): FormInterface
    {
        if ($venue->isArchived()) {
            return $this->createFormBuilder($venue)
                ->add(
                    'restore',
                    SubmitType::class,
                    ['label' => 'Spielort wiederherstellen']
                )
                ->setMethod('POST')
                ->setAction($this->generateUrl('venue_restore', ['id' => $venue->getId()]))
                ->getForm();
        }

        return $this->createFormBuilder($venue)
            ->add(
                'archive',
                SubmitType::class,
                ['label' => 'Spielort archivieren', 'attr' => ['title' => 'archivierte Spielorte können wiederhergestellt werden']]
            )
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('venue_archive', ['id' => $venue->getId()]))
            ->getForm();
    }
}
