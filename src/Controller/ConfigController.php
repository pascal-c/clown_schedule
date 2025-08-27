<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ConfigFormType;
use App\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractProtectedController
{
    public function __construct(private ConfigRepository $configRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/config', name: 'config', methods: ['GET', 'PUT'])]
    public function edit(Request $request): Response
    {
        $this->adminOnly();
        $form = $this->createForm(ConfigFormType::class, $this->configRepository->find());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Yep! Einstellungen wurden gespeichert.');

            return $this->redirect($this->generateUrl('config'));
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Hach! Das hat irgendwie nicht funktioniert.');
        }

        return $this->render('config/edit.html.twig', [
            'active' => 'config',
            'form' => $form->createView(),
        ]);
    }
}
