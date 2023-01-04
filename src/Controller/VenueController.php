<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueFormType;
use App\Repository\VenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VenueController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private VenueRepository $venueRepository)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/venues', name: 'venue_index', methods: ['GET'])]
    public function index(): Response 
    {
        return $this->render('venue/index.html.twig', [
            'venues' => $this->venueRepository->all(),
            'active' => 'venue',
        ]);
    }

    #[Route('/venues/new', name: 'venue_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->adminOnly();

        $venue = new Venue();

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

        return $this->renderForm('venue/new.html.twig', [
            'form' => $form,
            'active' => 'venue',
        ]);
    }

    #[Route('/venues/edit/{id}', name: 'venue_edit', methods: ['GET', 'PATCH'])]
    public function edit(Request $request, int $id): Response
    {
        $this->adminOnly();

        $venue = $this->venueRepository->find($id);

        $editForm = $this->createForm(VenueFormType::class, $venue, ['method' => 'PATCH']);
        $deleteForm = $this->createFormBuilder($venue)
            ->add('delete', SubmitType::class, 
                ['label' => 'Spielort löschen', 'attr' => array('onclick' => 'return confirm("Spielort endgültig löschen?")')])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('venue_delete', ['id' => $id]))
            ->getForm();

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Spielort wurde aktualisiert. Super!');
            return $this->redirectToRoute('venue_show', ['id' => $id]);
        } elseif ($editForm->isSubmitted()) {
            $this->addFlash('warning', 'Oh! Spielort konnte nicht aktualisiert werden.');
        }

        return $this->renderForm('venue/edit.html.twig', [
            'venue' => $venue,
            'form' => $editForm,
            'delete_form' => $deleteForm,
            'active' => 'venue',
        ]);
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

            $this->addFlash('success', 'Spielort '. $venue->getName() . ' wurde gelöscht. Danke fürs Aufräumen!');
            return $this->redirectToRoute('venue_index');
        }

        $this->addFlash('warning', 'Achtung! Spielort konnte nicht gelöscht werden.');
        return $this->redirectToRoute('venue_edit', ['id' => $venue->getId()]);
    }

    #[Route('/venues/{id}', name: 'venue_show', methods: ['GET'])]
    public function show(int $id): Response 
    {
        return $this->render('venue/show.html.twig', [
            'venue' => $this->venueRepository->find($id),
            'active' => 'venue',
        ]);
    }
}
