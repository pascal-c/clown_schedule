<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDateChangeRequest;
use App\Form\PlayDateGiveOffRequestAcceptFormType;
use App\Form\PlayDateGiveOffRequestCreateFormType;
use App\Mailer\PlayDateGiveOffRequestMailer;
use App\Repository\ClownRepository;
use App\Repository\PlayDateChangeRequestRepository;
use App\Repository\PlayDateRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateChangeService;
use App\Service\TimeService;
use App\Value\PlayDateChangeRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PlayDateGiveOffRequestController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository,
        private PlayDateRepository $playDateRepository,
        private TimeService $timeService,
        private PlayDateChangeRequestRepository $playDateChangeRequestRepository,
        private PlayDateChangeService $playDateChangeService,
        private PlayDateChangeRequestCloseInvalidService $playDateChangeRequestCloseInvalidService,
        private EntityManagerInterface $entityManager,
        private PlayDateGiveOffRequestMailer $mailer,
    ) {
    }

    #[Route('/play_date/{id}/give-off_request/new', name: 'play_date_new_give-off_request', methods: ['GET', 'POST'])]
    public function new(Request $request, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);

        $form = $this->createForm(PlayDateGiveOffRequestCreateFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $playDateChangeRequest = (new PlayDateChangeRequest())
                ->setPlayDateToGiveOff($playDate)
                ->setRequestedBy($this->getCurrentClown())
                ->setRequestedAt($this->timeService->now())
                ->setType(PlayDateChangeRequestType::GIVE_OFF);

            $this->entityManager->persist($playDateChangeRequest);
            $this->entityManager->flush();

            $this->mailer->sendGiveOffRequestMail($playDateChangeRequest, $formData['comment']);

            $this->addFlash('success', 'Deine Abgabe-Anfrage wurde erfolgreich gestellt. Alle aktiven Clowns bekommen ein Email. Bestimmt wird sich eine:r finden!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Irgendwie hat das nicht geklappt. Tut mir leid!');
        }

        return $this->render('play_date_change_request/new_give-off_request.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_date_give-off_request/{id}/accept', name: 'play_date_give-off_request_accept', methods: ['GET', 'POST'])]
    public function accept(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw new NotFoundHttpException();
        }

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Wahrscheinlich ist Dir jemensch zuvorgekommen. Schade!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateGiveOffRequestAcceptFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->accept($playDateChangeRequest, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->mailer->sendAcceptGiveOffRequestMail($playDateChangeRequest, $form->getData()['comment']);
            $this->mailer->sendInformPartnersAboutChangeMail($playDateChangeRequest);

            $this->addFlash('success', 'Super! Du hast den Spieltermin übernommen, Danke! Die anfragende Person wird per Email informiert.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/accept_give-off_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
