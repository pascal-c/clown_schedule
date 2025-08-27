<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ClownAvailabilityRepository;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\Rater;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractProtectedController
{
    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private PlayDateRepository $playDateRepository,
        private MonthRepository $monthRepository,
        private SubstitutionRepository $substitutionRepository,
        private ClownRepository $clownRepository,
        private ConfigRepository $configRepository,
        private Rater $rater,
    ) {
    }

    #[Route('/statistics/infinity', name: 'statistics_infinity', methods: ['GET'])]
    public function showInfinity(): Response
    {
        $clownsWithTotalCount = $this->clownRepository->allWithTotalPlayDateCounts();
        $clownsWithSuperCount = $this->clownRepository->allWithSuperPlayDateCounts();

        foreach ($clownsWithTotalCount as $k => $clownWithTotalCount) {
            $clownsWithTotalCount[$k]['superCount'] = 0;
            foreach ($clownsWithSuperCount as $clownWithSuperCount) {
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
        $substitutionTimeSlots = $this->substitutionRepository->byMonth($month);

        $substitutions = [];
        $plays = [];
        foreach ($clownAvailabilities as $availability) {
            $plays[$availability->getClown()->getId()] = 0;
            $substitutions[$availability->getClown()->getId()] = 0;
        }
        foreach ($playDates as $playDate) {
            foreach ($playDate->getPlayingClowns() as $clown) {
                if (isset($plays[$clown->getId()])) {
                    ++$plays[$clown->getId()];
                }
            }
        }
        foreach ($substitutionTimeSlots as $substitutionTimeSlot) {
            $substitutionClownId = $substitutionTimeSlot->getSubstitutionClown()?->getId();
            if (!is_null($substitutionClownId)) {
                if (isset($substitutions[$substitutionClownId])) {
                    ++$substitutions[$substitutionClownId];
                }
            }
        }

        return $this->render('statistics/per_month.html.twig', [
            'month' => $month,
            'clownAvailabilities' => $clownAvailabilities,
            'currentPlays' => $plays,
            'currentPlayDatesCount' => count($playDates),
            'currentSubstitutions' => $substitutions,
            'active' => 'statistics',
            'showMaxPerWeek' => $this->configRepository->isFeatureMaxPerWeekActive(),
            'points' => $this->rater->pointsPerCategory($month),
        ]);
    }
}
