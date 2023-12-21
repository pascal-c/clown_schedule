<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDateChangeRequest;
use App\Form\PlayDateSwapRequestAcceptFormType;
use App\Form\PlayDateSwapRequestDeclineFormType;
use App\Form\PlayDateSwapRequestFormType;
use App\Mailer\PlayDateSwapRequestMailer;
use App\Repository\ClownRepository;
use App\Repository\PlayDateChangeRequestRepository;
use App\Repository\PlayDateRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\PlayDateChangeService;
use App\Service\TimeService;
use App\Value\PlayDateChangeRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PlayDateChangeRequestController extends AbstractController
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
    )
    {}

    #[Route('/play_date/{id}/swap_request/new', name: 'play_date_new_swap_request', methods: ['GET', 'POST'])]
    public function newSwapRequest(Request $request, int $id): Response
    {
        $playDate = $this->playDateRepository->find($id);
        
        $form = $this->createForm(PlayDateSwapRequestFormType::class, null, [
            'currentClown' => $this->getCurrentClown(),
            'playDateToGiveOff' => $playDate,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            list($playDateWantedId, $clownId) = explode('-', $formData['playDateAndClown']);
            $playDateChangeRequest = new PlayDateChangeRequest();
            $playDateChangeRequest
                ->setPlayDateToGiveOff($playDate)
                ->setRequestedBy($this->getCurrentClown())
                ->setPlayDateWanted($this->playDateRepository->find((int) $playDateWantedId))
                ->setRequestedTo($this->clownRepository->find((int) $clownId))
                ->setRequestedAt($this->timeService->now())
                ->setType(PlayDateChangeRequestType::SWAP)
                ;

            $this->entityManager->persist($playDateChangeRequest);
            $this->entityManager->flush();

            $this->mailer->sendSwapRequestMail($playDateChangeRequest, $formData['comment']);

            $this->addFlash('success', 'Tauschanfrage wurde erfolgreich gestellt. Hoffentlich klappt das!');
            return $this->redirectToRoute('play_date_show', ['id' => $playDate->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('warning', 'Irgendwie konnte die Tauschanfrage nicht gestellt werden. Tut mir leid!');
        }

        return $this->render('play_date_change_request/new_swap_request.html.twig', [
            'playDate' => $playDate,
            'form' => $form,
        ]);
    }

    #[Route('/play_date_change_request/{id}/accept', name: 'play_date_change_request_accept', methods: ['GET', 'POST'])]
    public function acceptChangeRequest(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw(new NotFoundHttpException);
        } elseif (
            !is_null($playDateChangeRequest->getRequestedTo())
            && $playDateChangeRequest->getRequestedTo() !== $this->getCurrentClown()
        ) {
            throw($this->createAccessDeniedException('Betrug! Nur die angefragte Person darf den Tausch annehmen!'));
        } 
        
        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);
        
        if (!$playDateChangeRequest->isWaiting()) {
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Wahrscheinlich ist Dir jemensch zuvorgekommen. Schade!');
            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        } 
        
        $form = $this->createForm(PlayDateSwapRequestAcceptFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->accept($playDateChangeRequest, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->mailer->sendAcceptSwapRequestMail($playDateChangeRequest, $form->getData()['comment']);

            $this->addFlash('success', 'Yippieh! Spieltermin wurde getauscht!');
            
            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/accept_swap_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'playDateWanted' => $playDateChangeRequest->getPlayDateWanted(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    #[Route('/play_date_change_request/{id}/decline', name: 'play_date_change_request_decline', methods: ['GET', 'POST'])]
    public function declineChangeRequest(Request $request, int $id): Response
    {
        $playDateChangeRequest = $this->playDateChangeRequestRepository->find($id);
        if (is_null($playDateChangeRequest)) {
            throw(new NotFoundHttpException);
        } elseif (
            !is_null($playDateChangeRequest->getRequestedTo())
            && $playDateChangeRequest->getRequestedTo() !== $this->getCurrentClown()
        ) {
            throw($this->createAccessDeniedException('Betrug! Nur die angefragte Person darf den Tausch ablehnen!'));
        } 
        
        $this->playDateChangeRequestCloseInvalidService->closeIfInvalid($playDateChangeRequest);
        
        if (!$playDateChangeRequest->isWaiting()) {
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Die Tauschanfrage ist bereits geschlossen worden.');
            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        } 
        
        $form = $this->createForm(PlayDateSwapRequestDeclineFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playDateChangeService->decline($playDateChangeRequest, $this->getCurrentClown());
            $this->entityManager->flush();

            $this->mailer->sendDeclineSwapRequestMail($playDateChangeRequest, $form->getData()['comment']);

            $this->addFlash('success', 'Gut gemacht! Tauschanfrage wurde erfolgreich abgelehnt! Du kannst Dich ja hier nicht um alles kümmern!');
            
            return $this->redirectToRoute('play_date_show', ['id' => $playDateChangeRequest->getPlayDateToGiveOff()->getId()]);
        }

        return $this->render('play_date_change_request/accept_swap_request.html.twig', [
            'playDateToGiveOff' => $playDateChangeRequest->getPlayDateToGiveOff(),
            'playDateWanted' => $playDateChangeRequest->getPlayDateWanted(),
            'requestedBy' => $playDateChangeRequest->getRequestedBy(),
            'form' => $form,
        ]);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
