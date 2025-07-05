<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Fee;
use App\Entity\PlayDate;
use App\Form\FeeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayDateFeeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/play_dates/{id}/fee/edit', name: 'play_date_fee_edit', methods: ['GET', 'POST'])]
    public function edit(PlayDate $playDate, Request $request): Response
    {
        $this->adminOnly();

        if (!$playDate->isPaid()) {
            throw $this->createAccessDeniedException('Dieser Spieltermin ist unbezahlt.');
        }

        $fee = $playDate->getPlayDateFee() ?? new Fee();
        $playDate->setFee($fee);

        if ($request->getMethod() && !$playDate->hasIndividualFee() && $playDate->hasVenueFee()) {
            $lastFee = $playDate->getVenue()->getFeeFor($playDate->getDate());
            $fee->setFeeAlternative($lastFee->getFeeAlternative());
            $fee->setFeeStandard($lastFee->getFeeStandard());
            $fee->setKilometers($lastFee->getKilometers());
            $fee->setFeePerKilometer($lastFee->getFeePerKilometer());
            $fee->setKilometersFeeForAllClowns($lastFee->isKilometersFeeForAllClowns());
        }
        $form = $this->createForm(FeeFormType::class, $fee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($fee);
            $this->entityManager->flush();

            $this->addFlash('success', 'Yes, Honorar gespeichert!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Das stimmt was nicht!');
        }

        return $this->render('play_date/fee/edit.html.twig', [
            'form' => $form,
            'active' => 'schedule',
            'playDate' => $playDate,
        ]);
    }
}
