<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Clown;
use App\Entity\Substitution;
use App\Repository\SubstitutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;

class SubstitutionController extends AbstractProtectedController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        private SubstitutionRepository $substitutionRepository,
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/substitutions/{date}/{daytime}', name: 'substitution_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, DateTimeImmutable $date, string $daytime): Response
    {
        $this->adminOnly();

        $substitution = $this->substitutionRepository->find($date, $daytime);
        if (is_null($substitution)) {
            $substitution = new Substitution();
            $substitution->setDate($date)->setDaytime($daytime);
        }

        $form = $this->createFormBuilder($substitution)
            ->add('substitutionClown', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Springer:in',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();
            $this->entityManager->persist($substitution);
            $this->entityManager->flush();

            $this->addFlash('success', 'Springer:in wurde erfolgreich gespeichert.');

            return $this->redirectToRoute('schedule');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Springer:in konnte nicht gespeichert werden.');
        }

        return $this->render('substitution/edit.html.twig', [
            'month' => $substitution->getMonth(),
            'form' => $form,
            'substitution' => $substitution,
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
