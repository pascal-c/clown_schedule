<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Month;
use App\Entity\TimeSlotInterface;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Repository\TimeSlotRepository;
use App\Service\TimeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private TimeSlotRepository $timeSlotRepository, 
        private PlayDateRepository $playDateRepository,
        private TimeService $timeService,
        private ClownAvailabilityRepository $clownAvailabilityRepository
    ) {}

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
            $this->addFlash('danger', 
                sprintf('Hey %s, Du musst DRINGEND noch Deine Fehlzeiten für %s eintragen', 
                    $currentClown->getName(), $nextMonth->getLabel())
            );
        }
        if ($currentClown->isActive() && $today >= $this->timeService->middleOfCurrentMonth() && !$afterNextMonthFilled) {
            $this->addFlash('warning', 
                sprintf('Hey %s, Du musst noch Deine Fehlzeiten für %s eintragen', 
                    $currentClown->getName(), $afterNextMonth->getLabel())
            );
        }

        $playDates = $this->playDateRepository->futureByClown($currentClown);
        $timeSlots = $this->timeSlotRepository->futureByClown($currentClown);
        $dates = array_merge($playDates, $timeSlots);
        usort($dates, 
            fn(TimeSlotInterface $a, TimeSlotInterface $b) => 
                $a->getDate() == $b->getDate()
                ?
                $a->getDaytime() <=> $b->getDaytime()
                :
                $a->getDate() <=> $b->getDate()
        );
        return $this->render('dashboard/index.html.twig', [
            'dates' => $dates,
            'nextMonth' => $nextMonth,
            'afterNextMonth' => $afterNextMonth,
            'nextMonthFilled' => $nextMonthFilled,
            'afterNextMonthFilled' => $afterNextMonthFilled,
            'feedbackUrl' => $this->getParameter('app.feedback_url'),
            'active' => 'dashboard',
        ]);
    }
}
