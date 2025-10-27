<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Clown;
use App\Form\ClownVenuePreferencesFormType;
use App\Repository\ClownRepository;
use App\Repository\VenueRepository;
use App\Service\ClownVenuePreferenceGenerator;
use App\Service\SessionService;
use App\Value\Preference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClownVenuePreferencesController extends AbstractProtectedController
{
    public function __construct(
        private SessionService $sessionService,
        private ClownRepository $clownRepository,
        private VenueRepository $venueRepository,
        private EntityManagerInterface $entityManager,
        private ClownVenuePreferenceGenerator $clownVenuePreferenceGenerator,
    ) {
    }

    #[Route('/clown-venue-preferences/{clownId}', name: 'clown_venue_preferences_show', methods: ['GET'])]
    public function show(?int $clownId = null): Response
    {
        $this->sessionService->setActiveClownId($clownId);
        $clowns = $clownId ? [$this->clownRepository->find($clownId)] : $this->clownRepository->allActive();
        $venues = $this->venueRepository->active();

        return $this->render('clown_venue_preferences/show.html.twig', [
            'clowns' => $clowns,
            'venues' => $venues,
            'showAll' => (null == $clownId),
        ]);
    }

    #[Route('/clown-venue-preferences/{clownId}/edit', name: 'clown_venue_preferences_edit', methods: ['GET'])]
    public function edit(#[MapEntity(id: 'clownId')] Clown $clown): Response
    {
        $this->adminOrCurrentClownOnly($clown);

        $this->clownVenuePreferenceGenerator->generateMissingPreferences($clown);
        $this->entityManager->flush();

        $form = $this->createForm(ClownVenuePreferencesFormType::class, $clown);

        return $this->render('clown_venue_preferences/form.html.twig', [
            'clown' => $clown,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clown-venue-preferences/{clownId}/edit', methods: ['POST'])]
    public function save(
        Request $request,
        #[MapEntity(id: 'clownId')] Clown $clown,
    ): Response {
        $this->adminOrCurrentClownOnly($clown);

        $form = $this->createForm(ClownVenuePreferencesFormType::class, $clown);

        $form->handleRequest($request);
        if ($form->isValid()) {
            foreach ($clown->getClownVenuePreferences() as $venuePreference) {
                $fieldName = 'clownVenuePreferences'.$venuePreference->getId();
                $newPreference = Preference::from($form->get($fieldName)->getData());
                $venuePreference->setPreference($newPreference);
            }
            $this->entityManager->flush();
            $this->addFlash('success', 'Deine Präferenzen wurden gespeichert. Vielen Dank!');

            return $this->redirectToRoute('clown_venue_preferences_show', ['clownId' => $clown->getId()]);
        }

        $this->addFlash('warning', 'Beim Speichern Deiner Präferenzen gab es leider ein Problem...');

        return $this->render('clown_venue_preferences/form.html.twig', [
            'clown' => $clown,
            'form' => $form->createView(),
            'venues' => $this->venueRepository->active(),
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->sessionService->setClownConstraintsNavigationKey('venue_preferences');

        return parent::render($view, array_merge($parameters, ['active' => 'clown_constraints']), $response);
    }
}
