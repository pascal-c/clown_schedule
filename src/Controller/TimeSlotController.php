<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Clown;
use App\Entity\TimeSlot;
use App\Repository\TimeSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimeSlotController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine, 
        private TimeSlotRepository $timeSlotRepository
    )
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/time_slots/{date}/{daytime}', name: 'time_slot_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, \DateTimeImmutable $date, string $daytime): Response 
    {
        $this->adminOnly();
        
        $timeSlot = $this->timeSlotRepository->find($date, $daytime);
        if (is_null($timeSlot)) {
            $timeSlot = new TimeSlot;
            $timeSlot->setDate($date)->setDaytime($daytime);
        }
    
        $form = $this->createFormBuilder($timeSlot)
            ->add('substitutionClown', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Springer',
                'expanded' => true,
                'multiple' => false,
            ])        
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT')
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();
            $this->entityManager->persist($timeSlot);
            $this->entityManager->flush();

            $this->addFlash('success', 'Springer wurde erfolgreich gespeichert.');
            return $this->redirectToRoute('schedule');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Springer konnte nicht gespeichert werden.');
        }

        return $this->render('time_slot/edit.html.twig', [
            'month' => $timeSlot->getMonth(),
            'form' => $form,
            'timeSlot' => $timeSlot,
        ]);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
