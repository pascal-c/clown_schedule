<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Form\PlayDateTakeOverRequestAcceptFormType;
use App\Form\PlayDateTakeOverRequestCancelFormType;
use App\Form\PlayDateTakeOverRequestCreateFormType;
use App\Guard\PlayDateGuard;
use App\Mailer\PlayDateTakeOverRequestMailer;
use App\Repository\ClownRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateChangeService;
use App\Service\TimeService;
use App\Value\PlayDateChangeRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlayDateTakeOverRequestController extends AbstractProtectedController
{
    public function __construct(
        private ClownRepository $clownRepository,
        private TimeService $timeService,
        private PlayDateChangeService $playDateChangeService,
        private PlayDateChangeRequestCloseInvalidService $playDateChangeRequestCloseInvalidService,
        private EntityManagerInterface $entityManager,
        private PlayDateTakeOverRequestMailer $mailer,
        private PlayDateGuard $playDateGuard,
    ) {
    }

    #[Route('/play_date/{id}/take-over_request/new', name: 'play_date_new_take-over_request', methods: ['GET', 'POST'])]
    public function new(Request $request, PlayDate $playDate): Response
    {
        $this->checkAuthorization($this->playDateGuard->canAssign($playDate));

        $form = $this->createForm(PlayDateTakeOverRequestCreateFormType::class, null, [
            'playDateToGiveOff' => $playDate,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $requestedToList = match($formData['requestedTo']) {
                'all' => [null],
                'team' => $playDate->getVenue()->getTeam()->toArray(),
                default => [$this->clownRepository->find((int) $formData['requestedTo'])],
            };

            $playDateChangeRequests = [];
            foreach ($requestedToList as $requestedTo) {
                $playDateChangeRequest = (new PlayDateChangeRequest())
                    ->setPlayDateToGiveOff($playDate)
                    ->setRequestedBy($this->getCurrentClown())
                    ->setRequestedTo($requestedTo)
                    ->setRequestedAt($this->timeService->now())
                    ->setType(PlayDateChangeRequestType::TAKE_OVER);

                $this->entityManager->persist($playDateChangeRequest);
                $playDateChangeRequests[] = $playDateChangeRequest;
            }

            $this->entityManager->flush();

            foreach ($playDateChangeRequests as $playDateChangeRequest) {
                $this->mailer->sendTakeOverRequestMail($playDateChangeRequest, $formData['comment']);
            }

            $this->addFlash('success', 'Deine Übernahme-Anfrage wurde erfolgreich gestellt. Alle angefragten Clowns bekommen ein Email. Bestimmt wird sich eine:r finden!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Irgendwie hat das nicht geklappt. Tut mir leid!');
        }

        return $this->render('play_date_change_request/new_take-over_request.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_date_take-over_request/{id}/accept', name: 'play_date_take-over_request_accept', methods: ['GET', 'POST'])]
    public function accept(Request $request, PlayDateChangeRequest $playDateChangeRequest): Response
    {
        $this->checkAuthorization($playDateChangeRequest->canAccept($this->getCurrentClown()));

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Wahrscheinlich ist Dir jemensch zuvorgekommen. Schade!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateTakeOverRequestAcceptFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->accept($playDateChangeRequest, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->mailer->sendInformPartnersAboutChangeMail($playDateChangeRequest);

            $this->addFlash('success', 'Super! Du hast den Spieltermin übernommen, vielen Dank!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/accept_take-over_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    #[Route('/play_date_take-over_request/{id}/cancel', name: 'play_date_take-over_request_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, PlayDateChangeRequest $playDateChangeRequest): Response
    {
        $this->checkAuthorization($playDateChangeRequest->canCancel($this->getCurrentClown()));

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Die Anfrage ist bereits geschlossen worden.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateTakeOverRequestCancelFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->close($playDateChangeRequest);
            $this->entityManager->flush();

            $this->addFlash('success', 'Ok! Anfrage wurde erfolgreich geschlossen!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/cancel_take-over_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'requestedTo' => $playDateChangeRequest->getRequestedTo(),
            'form' => $form,
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
