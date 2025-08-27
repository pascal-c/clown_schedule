<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Venue;
use App\Form\VenueContactFormType;
use App\Repository\VenueRepository;
use App\Service\TimeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VenueContactController extends AbstractProtectedController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private VenueRepository $venueRepository, private TimeService $timeService)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/venues/{id}/contacts', name: 'venue_contact_index', methods: ['GET'])]
    public function index(Venue $venue): Response
    {
        return $this->render('venue/contact/index.html.twig', [
            'venue' => $venue,
            'active' => 'venue',
        ]);
    }

    #[Route('/venues/{id}/contacts/new', name: 'venue_contact_new', methods: ['GET', 'POST'])]
    public function new(Venue $venue, Request $request): Response
    {
        $this->adminOnly();

        $contact = new Contact();
        $venue->addContact($contact);

        $form = $this->createForm(VenueContactFormType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Schön! Neuen Kontakt erfolgreich angelegt!');

            return $this->redirectToRoute('venue_contact_index', ['id' => $venue->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Da ist irgendwas schiefgegangen. Ich verstehe es auch nicht!');
        }

        return $this->render('venue/contact/new.html.twig', [
            'form' => $form,
            'active' => 'venue',
            'venue' => $venue,
        ]);
    }

    #[Route('/venues/{id}/contacts/{contact_id}/edit', name: 'venue_contact_edit', methods: ['GET', 'PUT'])]
    public function edit(Venue $venue, #[MapEntity(id: 'contact_id')] Contact $contact, Request $request): Response
    {
        $this->adminOnly();

        $form = $this->createForm(VenueContactFormType::class, $contact, ['method' => 'PUT']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Schön! Kontakt erfolgreich aktualisiert!');

            return $this->redirectToRoute('venue_contact_index', ['id' => $venue->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Das Aktualisieren hat leider nicht geklappt!');
        }

        return $this->render('venue/contact/edit.html.twig', [
            'form' => $form,
            'deleteForm' => $this->getDeleteForm($venue, $contact),
            'active' => 'venue',
            'venue' => $venue,
        ]);
    }

    #[Route('/venues/{id}/contacts/{contact_id}', name: 'venue_contact_delete', methods: ['DELETE'])]
    public function delete(Venue $venue, #[MapEntity(id: 'contact_id')] Contact $contact, Request $request): Response
    {
        $this->adminOnly();

        $deleteForm = $this->getDeleteForm($venue, $contact);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->entityManager->remove($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Kontakt wurde erfolgreich gelöscht.');

            return $this->redirectToRoute('venue_contact_index', ['id' => $venue->getId()]);
        }

        $this->addFlash('warning', 'Kontakt konnte nicht gelöscht werden.');

        return $this->redirectToRoute('clown_edit', ['id' => $venue->getId(), 'contact_id' => $contact->getId()]);
    }

    private function getDeleteForm(Venue $venue, Contact $contact): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder($contact)
            ->add('delete', SubmitType::class, ['label' => 'Kontakt löschen', 'attr' => ['onclick' => 'return confirm("Kontakt endgültig löschen?")']])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('venue_contact_delete', ['id' => $venue->getId(), 'contact_id' => $contact->getId()]))
            ->getForm();
    }
}
