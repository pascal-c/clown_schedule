<?php

namespace App\Controller;

use App\Entity\Clown;
use App\Form\ClownFormType;
use App\Repository\ClownRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClownController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private ClownRepository $clownRepository)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/clowns', name: 'clown_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('clown/index.html.twig', [
            'clowns' => $this->clownRepository->all(),
            'active' => 'clown',
        ]);
    }

    #[Route('/clowns/new', name: 'clown_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->adminOnly();

        $clown = new Clown();
        $form = $this->createForm(ClownFormType::class, $clown, ['method' => 'POST']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $clown = $form->getData();

            $this->entityManager->persist($clown);
            $this->entityManager->flush();

            $this->addFlash('success', 'Clown wurde erfolgreich angelegt.');

            return $this->redirectToRoute('clown_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Clown konnte nicht angelegt werden.');
        }

        return $this->render('clown/new.html.twig', [
            'form' => $form,
            'active' => 'clown',
        ]);
    }

    #[Route('/clowns/{id}', name: 'clown_edit', methods: ['GET', 'PATCH'])]
    public function edit(Request $request, int $id): Response
    {
        $this->adminOnly();

        $clown = $this->clownRepository->find($id);

        $form = $this->createForm(ClownFormType::class, $clown, ['method' => 'PATCH']);
        $deleteForm = $this->createFormBuilder($clown)
            ->add('delete', SubmitType::class,
                ['label' => 'Clown löschen', 'attr' => ['onclick' => 'return confirm("Clown endgültig löschen?")']])
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $clown = $form->getData();
            $clown->setIsActive($form['isActive']->isSubmitted());
            $clown->setIsAdmin($form['isAdmin']->isSubmitted());
            $this->entityManager->flush();

            $this->addFlash('success', 'Clown wurde erfolgreich gespeichert.');

            return $this->redirectToRoute('clown_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Clown konnte nicht gespeichert werden.');
        }

        return $this->render('clown/edit.html.twig', [
            'form' => $form,
            'delete_form' => $deleteForm,
            'active' => 'clown',
        ]);
    }

    #[Route('/clowns/{id}', name: 'clown_delete', methods: ['DELETE'])]
    public function delete(Request $request, $id): Response
    {
        $this->adminOnly();

        $clown = $this->clownRepository->find($id);

        $deleteForm = $this->createFormBuilder($clown)
            ->add('delete', SubmitType::class, ['label' => 'Clown löschen'])
            ->setMethod('DELETE')
            ->getForm();
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->entityManager->remove($clown);
            $this->entityManager->flush();

            $this->addFlash('success', 'Clown wurde erfolgreich gelöscht.');

            return $this->redirectToRoute('clown_index');
        }

        $this->addFlash('warning', 'Clown konnte nicht gelöscht werden.');

        return $this->redirectToRoute('clown_edit', ['id' => $clown->getId()]);
    }
}
