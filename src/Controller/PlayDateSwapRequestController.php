<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDateChangeRequest;
use App\Form\PlayDateSwapRequestAcceptFormType;
use App\Form\PlayDateSwapRequestCloseFormType;
use App\Form\PlayDateSwapRequestCreateFormType;
use App\Form\PlayDateSwapRequestDeclineFormType;
use App\Mailer\PlayDateSwapRequestMailer;
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

class PlayDateSwapRequestController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository,
        private PlayDateRepository $playDateRepository,
        private TimeService $timeService,
        private PlayDateChangeRequestRepository $playDateChangeRequestRepository,
        private PlayDateChangeService $playDateChangeService,
        private PlayDateChangeRequestCloseInvalidService $playDateChangeRequestCloseInvalidService,
        private EntityManagerInterface $entityManager,
        private PlayDateSwapRequestMailer $mailer,
    ) {
    }

    #[Route('/play_date/{id}/swap_request/new', name: 'play_date_new_swap_request', methods: ['GET', 'POST'])]
    public function new(Request $request, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);

        $form = $this->createForm(PlayDateSwapRequestCreateFormType::class, null, [
            'currentClown' => $this->getCurrentClown(),
            'playDateToGiveOff' => $playDate,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            list($playDateWantedId, $clownId) = explode('-', $formData['playDateAndClown']);
            $requestedTo = $this->clownRepository->find((int) $clownId);
            $playDateChangeRequest = new PlayDateChangeRequest();
            $playDateChangeRequest
                ->setPlayDateToGiveOff($playDate)
                ->setRequestedBy($this->getCurrentClown())
                ->setPlayDateWanted($this->playDateRepository->find((int) $playDateWantedId))
                ->setRequestedTo($requestedTo)
                ->setRequestedAt($this->timeService->now())
                ->setType(PlayDateChangeRequestType::SWAP)
            ;

            $this->entityManager->persist($playDateChangeRequest);
            $this->entityManager->flush();

            $this->mailer->sendSwapRequestMail($playDateChangeRequest, $formData['comment']);

            $this->addFlash('success', 'Tauschanfrage wurde erfolgreich gestellt. '.$requestedTo->getName().' hat eine Email bekommen. Hoffentlich klappt das!');
            $this->addFlash('info', 'Übrigens: Du kannst für einen Spieltermin auch mehrere Tauschanfragen parallel stellen! Sobald eine der Tauschanfragen angenommen wird, werden die anderen automtisch geschlossen.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Irgendwie konnte die Tauschanfrage nicht gestellt werden. Tut mir leid!');
        }

        return $this->render('play_date_change_request/new_swap_request.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_date_swap_request/{id}/accept', name: 'play_date_swap_request_accept', methods: ['GET', 'POST'])]
    public function accept(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw new NotFoundHttpException();
        } elseif (
            !is_null($playDateChangeRequest->getRequestedTo())
            && $playDateChangeRequest->getRequestedTo() !== $this->getCurrentClown()
        ) {
            throw $this->createAccessDeniedException('Betrug! Nur die angefragte Person darf den Tausch annehmen!');
        }

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Wahrscheinlich ist Dir jemensch zuvorgekommen. Schade!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateSwapRequestAcceptFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->accept($playDateChangeRequest, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->mailer->sendAcceptSwapRequestMail($playDateChangeRequest, $form->getData()['comment']);

            $this->addFlash('success', 'Yippieh! Spieltermin wurde getauscht! Die anfragende Person wird per Email informiert.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/answer_swap_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'playDateWanted' => $playDateChangeRequest->getPlayDateWanted(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    #[Route('/play_date_swap_request/{id}/decline', name: 'play_date_swap_request_decline', methods: ['GET', 'POST'])]
    public function decline(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw new NotFoundHttpException();
        } elseif (
            !is_null($playDateChangeRequest->getRequestedTo())
            && $playDateChangeRequest->getRequestedTo() !== $this->getCurrentClown()
        ) {
            throw $this->createAccessDeniedException('Betrug! Nur die angefragte Person darf den Tausch ablehnen!');
        }

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Die Tauschanfrage ist bereits geschlossen worden.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateSwapRequestDeclineFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->decline($playDateChangeRequest);
            $this->entityManager->flush();

            $this->mailer->sendDeclineSwapRequestMail($playDateChangeRequest, $form->getData()['comment']);

            $this->addFlash('success', 'Gut gemacht! Tauschanfrage wurde erfolgreich abgelehnt! Du kannst Dich ja hier nicht um alles kümmern!');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/answer_swap_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'playDateWanted' => $playDateChangeRequest->getPlayDateWanted(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    #[Route('/play_date_swap_request/{id}/cancel', name: 'play_date_swap_request_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw new NotFoundHttpException();
        } elseif ($playDateChangeRequest->getRequestedBy() !== $this->getCurrentClown()) {
            throw $this->createAccessDeniedException('Betrug! Nur die anfragende Person darf den Tausch abbrechen!');
        }

        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);

        if (!$playDateChangeRequest->isWaiting()) {
            $this->entityManager->flush();
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Die Tauschanfrage ist bereits geschlossen worden.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        $form = $this->createForm(PlayDateSwapRequestCloseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->close($playDateChangeRequest);
            $this->entityManager->flush();

            $this->mailer->sendCancelSwapRequestMail($playDateChangeRequest, $form->getData()['comment']);

            $this->addFlash('success', 'Ok! Tauschanfrage wurde erfolgreich geschlossen! Die angefragte Person wird per Email informiert.');

            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/cancel_swap_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'playDateWanted' => $playDateChangeRequest->getPlayDateWanted(),
            'requestedTo' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
