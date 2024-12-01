<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Venue;
use App\Entity\VenueFee;
use App\Form\VenueFeeFormType;
use App\Repository\VenueRepository;
use App\Service\TimeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VenueFeeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private VenueRepository $venueRepository, private TimeService $timeService)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/venues/{id}/fees', name: 'venue_fee_index', methods: ['GET'])]
    public function index(Venue $venue): Response
    {
        $showEditLink = false;
        if ($firstFee = $venue->getFees()->first()) {
            $showEditLink = $this->timeService->firstOfMonth() <= $firstFee->getValidFrom();
        }

        return $this->render('venue/fee/index.html.twig', [
            'venue' => $venue,
            'active' => 'venue',
            'showEditLink' => $showEditLink,
        ]);
    }

    #[Route('/venues/{id}/fees/new', name: 'venue_fee_new', methods: ['GET', 'POST'])]
    public function new(Venue $venue, Request $request): Response
    {
        $this->adminOnly();

        $newFee = new VenueFee();
        if ($lastFee = $venue->getFees()->first()) {
            $newFee->setFeeByCar($lastFee->getFeeByCar());
            $newFee->setFeeByPublicTransport($lastFee->getFeeByPublicTransport());
            $newFee->setKilometers($lastFee->getKilometers());
            $newFee->setFeePerKilometer($lastFee->getFeePerKilometer());
            $newFee->setKilometersFeeForAllClowns($lastFee->isKilometersFeeForAllClowns());
        }
        $newFee = $newFee->setVenue($venue);

        $form = $this->createForm(VenueFeeFormType::class, $newFee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($newFee);
            $this->entityManager->flush();

            $this->addFlash('success', 'Großartig! Neues Honorar erfolgreich angelegt!');

            return $this->redirectToRoute('venue_fee_index', ['id' => $venue->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Das können wir so nicht machen!');
        }

        return $this->render('venue/fee/new.html.twig', [
            'form' => $form,
            'active' => 'venue',
            'venue' => $venue,
        ]);
    }

    #[Route('/venues/fees/{id}/edit', name: 'venue_fee_edit', methods: ['GET', 'POST'])]
    public function edit(VenueFee $venueFee, Request $request): Response
    {
        $this->adminOnly();

        $form = $this->createForm(VenueFeeFormType::class, $venueFee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Großartig! Honorar erfolgreich aktualisiert!');

            return $this->redirectToRoute('venue_fee_index', ['id' => $venueFee->getVenue()->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Das können wir so nicht machen!');
        }

        return $this->render('venue/fee/edit.html.twig', [
            'form' => $form,
            'active' => 'venue',
            'venue' => $venueFee->getVenue(),
        ]);
    }
}
