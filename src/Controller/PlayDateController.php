<?php
namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Form\PlayDateAssignClownsFormType;
use App\Form\PlayDateFormType;
use App\Repository\PlayDateRepository;
use App\Repository\VenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayDateController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private PlayDateRepository $playDateRepository)
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
        $playDate = new PlayDate();
        $venueId = $request->query->get('venue_id');
        if (isset($venueId)) {
            $venue = $venueRepository->find($venueId);
            $playDate->setVenue($venue);
            $playDate->setDaytime($venue->getDaytimeDefault());
        }
        else {
            $venue = null;
        }

        $form = $this->createForm(PlayDateFormType::class, $playDate);

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

        return $this->renderForm('play_date/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/play_dates/{id}', name: 'play_date_edit', methods: ['GET', 'PATCH'])]
    public function edit(Request $request, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);

        $editForm = $this->createForm(PlayDateFormType::class, $playDate, ['method' => 'PATCH']);
        $deleteForm = $this->createFormBuilder($playDate)
            ->add('delete', SubmitType::class, 
                ['label' => 'Spieltermin löschen', 'attr' => array('onclick' => 'return confirm("Spieltermin endgültig löschen?")')])
            ->setMethod('DELETE')
            ->getForm();

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $playDate = $editForm->getData();
            $this->entityManager->flush();

            $this->addFlash('success', 'Spieltermin wurde aktualisiert. Sehr gut!');
            return $this->redirectAfterSuccess($request->query->get('venue_id') ? $playDate->getVenue() : null);
        } elseif ($editForm->isSubmitted()) {
            $this->addFlash('warning', 'Hach! Spieltermin konnte irgendwie nicht aktualisiert werden.');
        }

        return $this->renderForm('play_date/edit.html.twig', [
            'form' => $editForm,
            'delete_form' => $deleteForm,
        ]);
    }

    #[Route('/play_dates/assign_clowns/{id}', name: 'play_date_assign_clowns', methods: ['GET', 'PATCH'])]
    public function assignClowns(Request $request, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);

        $form = $this->createForm(PlayDateAssignClownsFormType::class, $playDate, ['method' => 'PATCH']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Clowns wurden zugeordnet. Tip top!');
            return $this->redirectToRoute('schedule');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Mist, das hat nicht geklappt!');
        }

        return $this->renderForm('play_date/assign_clowns.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_dates/{id}', name: 'play_date_delete', methods: ['DELETE'])]
    public function delete(Request $request, $id): Response
    {
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

    protected function renderForm(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::renderForm($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
