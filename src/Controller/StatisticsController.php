<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ClownAvailabilityRepository;
use App\Repository\ClownRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\TimeSlotRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private PlayDateRepository $playDateRepository,
        private MonthRepository $monthRepository,
        private TimeSlotRepository $timeSlotRepository,
        private ClownRepository $clownRepository)
    {
    }

    #[Route('/statistics/infinity', name: 'statistics_infinity', methods: ['GET'])]
    public function showInfinity(): Response 
    {
        $clownsWithTotalCount = $this->clownRepository->allWithTotalPlayDateCounts();
        $clownsWithSuperCount = $this->clownRepository->allWithSuperPlayDateCounts();
        
        foreach($clownsWithTotalCount as $k => $clownWithTotalCount) {
            $clownsWithTotalCount[$k]['superCount'] = 0;
            foreach($clownsWithSuperCount as $clownWithSuperCount) {
                if ($clownWithSuperCount['clown'] === $clownWithTotalCount['clown']) {
                    $clownsWithTotalCount[$k]['superCount'] = $clownWithSuperCount['superCount'];
                }
            }
        }

        return $this->render('statistics/infinity.html.twig', [
            'month' => null,
            'clownsWithCounts' => $clownsWithTotalCount,
            'active' => 'statistics',
        ]);
    }

    #[Route('/statistics/{monthId}', name: 'statistics', methods: ['GET'])]
    public function showPerMonth(SessionInterface $session, Request $request, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
        $playDates = $this->playDateRepository->regularByMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $timeSlots = $this->timeSlotRepository->byMonth($month);

        $substitutions = [];
        $plays = [];
        foreach ($clownAvailabilities as $availability) { 
            $plays[$availability->getClown()->getId()] = 0;
            $substitutions[$availability->getClown()->getId()] = 0;
        }
        foreach($playDates as $playDate) {
            foreach($playDate->getPlayingClowns() as $clown) {
                if (!isset($plays[$clown->getId()])) {
                    $plays[$clown->getId()] = 0;
                }
                $plays[$clown->getId()]++;
            }
        }
        foreach($timeSlots as $timeSlot) {
            if (!is_null($timeSlot->getSubstitutionClown())) {
                $substitutions[$timeSlot->getSubstitutionClown()->getId()]++;
            }
        }

        return $this->render('statistics/per_month.html.twig', [
            'month' => $month,
            'clownAvailabilities' => $clownAvailabilities,
            'currentPlays' => $plays,
            'currentPlayDatesCount' => count($playDates),
            'currentSubstitutions' => $substitutions,
            'currentSubstitutionsNeededCount' => $this->playDateRepository->countRegularTimeSlotsPerMonth($month),
            'active' => 'statistics',
        ]);
    }
}
