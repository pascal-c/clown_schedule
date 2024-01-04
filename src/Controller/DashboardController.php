<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Month;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateChangeRequestRepository;
use App\Service\TimeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private TimeService $timeService,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private PlayDateChangeRequestRepository $playDateChangeRequestRepository,
    ) {
    }

    #[Route('/', name: 'root', methods: ['GET'])]
    public function root(): Response
    {
        $route = $this->getCurrentClown()->isAdmin() && !$this->getCurrentClown()->isActive()
            ? 'schedule' : 'dashboard';

        return $this->redirectToRoute($route);
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $today = $this->timeService->today();
        $currentMonth = new Month($today);
        $nextMonth = $currentMonth->next();
        $afterNextMonth = $nextMonth->next();
        $currentClown = $this->getCurrentClown();
        $nextMonthFilled = $this->clownAvailabilityRepository->find($nextMonth, $currentClown);
        $afterNextMonthFilled = $this->clownAvailabilityRepository->find($afterNextMonth, $currentClown);
        if ($currentClown->isActive() && !$nextMonthFilled) {
            $this->addFlash(
                'danger',
                sprintf(
                    'Hey %s, Du musst DRINGEND noch Deine Fehlzeiten für %s eintragen',
                    $currentClown->getName(),
                    $nextMonth->getLabel()
                )
            );
        }
        if ($currentClown->isActive() && $today >= $this->timeService->NearlyEndOfMonth() && !$afterNextMonthFilled) {
            $this->addFlash(
                'warning',
                sprintf(
                    'Hey %s, Du musst noch Deine Fehlzeiten für %s eintragen',
                    $currentClown->getName(),
                    $afterNextMonth->getLabel()
                )
            );
        }

        return $this->render('dashboard/index.html.twig', [
            'nextMonth' => $nextMonth,
            'afterNextMonth' => $afterNextMonth,
            'nextMonthFilled' => $nextMonthFilled,
            'afterNextMonthFilled' => $afterNextMonthFilled,
            'feedbackUrl' => $this->getParameter('app.feedback_url'),
            'active' => 'dashboard',
            'sentChangeRequests' => $this->playDateChangeRequestRepository->findSentRequestsWaiting($currentClown),
            'receivedChangeRequests' => $this->playDateChangeRequestRepository->findReceivedRequestsWaiting($currentClown),
        ]);
    }
}
