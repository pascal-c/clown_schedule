<?php

namespace App\Controller;

use App\Entity\Clown;
use App\Form\ClownFormType;
use App\Form\ClownBlockedClownsFormType;
use App\Mailer\AuthenticationMailer;
use App\Repository\ClownRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClownController extends AbstractProtectedController
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrine, private ClownRepository $clownRepository, private AuthenticationMailer $mailer)
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
            if ($form['send_invitation_email']->getData()) {
                $this->mailer->sendInvitationMail($clown, $this->getCurrentClown());
                $this->addFlash('success', 'Ich habe auch eine Einladungsemail an den Clown geschickt.');
            }

            return $this->redirectToRoute('clown_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Clown konnte nicht angelegt werden.');
        }

        return $this->render('clown/new.html.twig', [
            'form' => $form,
            'active' => 'clown',
        ]);
    }

    #[Route('/clowns/{id}/send_invitation_email', name: 'clown_send_invitation_email', methods: ['POST'])]
    public function sendInvitationEmail(Clown $clown): Response
    {
        $this->adminOnly();
        $this->mailer->sendInvitationMail($clown, $this->getCurrentClown());
        $this->addFlash('success', sprintf('Alles klar! Ich habe eine Einladungsemail an %s geschickt.', $clown->getName()));

        return $this->redirectToRoute('clown_index');
    }

    #[Route('/clowns/{id}', name: 'clown_edit', methods: ['GET', 'PUT'])]
    public function edit(Clown $clown, Request $request): Response
    {
        $this->adminOnly();

        $form = $this->createForm(ClownFormType::class, $clown, ['method' => 'PUT']);
        $deleteForm = $this->createFormBuilder($clown)
            ->add(
                'delete',
                SubmitType::class,
                ['label' => 'Clown löschen', 'attr' => ['onclick' => 'return confirm("Clown endgültig löschen?")']]
            )
            ->setMethod('DELETE')
            ->getForm();
        $sendInvitationForm = $this->createFormBuilder($clown)
            ->add(
                'send_invitation_email',
                SubmitType::class,
                ['label' => 'Einladungsemail senden', 'attr' => ['onclick' => 'return confirm("Jetzt Email senden?")']]
            )
            ->setAction($this->generateUrl('clown_send_invitation_email', ['id' => $clown->getId()]))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Clown wurde erfolgreich gespeichert.');

            return $this->redirectToRoute('clown_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Clown konnte nicht gespeichert werden.');
        }

        return $this->render('clown/edit.html.twig', [
            'clown' => $clown,
            'form' => $form,
            'delete_form' => $deleteForm,
            'send_invitation_form' => $clown->hasNoPassword() ? $sendInvitationForm : null,
            'active' => 'clown',
        ]);
    }

    #[Route('/clowns/{id}/blocked-clowns', name: 'clown_edit_blocked_clowns', methods: ['GET', 'PUT'])]
    public function editBlockedClowns(Clown $clown, Request $request): Response
    {
        $this->adminOnly();

        $form = $this->createForm(ClownBlockedClownsFormType::class, $clown, ['method' => 'PUT']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Gesperrte Clowns für '.$clown->getName().' wurden erfolgreich gespeichert.');

            return $this->redirectToRoute('clown_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Gesperrte Clowns konnten nicht gespeichert werden.');
        }

        return $this->render('clown/edit_blocked_clowns.html.twig', [
            'clown' => $clown,
            'form' => $form,
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
