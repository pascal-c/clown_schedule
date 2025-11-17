<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\Schedule;
use App\Gateway\RosterCalculatorGateway;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\FairPlayCalculator;
use App\Service\SessionService;
use App\Service\TimeService;
use App\Value\StatisticsForClownsType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractProtectedController
{
    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private PlayDateRepository $playDateRepository,
        private MonthRepository $monthRepository,
        private ScheduleRepository $scheduleRepository,
        private SubstitutionRepository $substitutionRepository,
        private ClownRepository $clownRepository,
        private ConfigRepository $configRepository,
        private RosterCalculatorGateway $rosterCalculatorGateway,
        private FairPlayCalculator $fairPlayCalculator,
        private TimeService $timeService,
        private SessionService $sessionService,
    ) {
    }

    #[Route('/statistics/infinity', name: 'statistics_infinity', methods: ['GET'])]
    public function showInfinity(#[MapQueryParameter] ?string $type = null): Response
    {
        return $this->showClownPropertyPercentage($type, null);
    }

    #[Route('/statistics/per_year/{year}', name: 'statistics_per_year', methods: ['GET'])]
    public function showPerYear(?string $year = null, #[MapQueryParameter] ?string $type = null): Response
    {
        $year ??= $this->timeService->currentYear();

        return $this->showClownPropertyPercentage($type, $year);
    }

    private function showClownPropertyPercentage(?string $type, ?string $year): Response
    {
        $years = range($this->playDateRepository->minYear(), $this->playDateRepository->maxYear());

        $clownsWithTotalCount = $this->clownRepository->allWithTotalPlayDateCounts($year);
        $clownsWithSuperCount = $this->clownRepository->allWithSuperPlayDateCounts($year);

        if ($type) {
            $currentType = StatisticsForClownsType::from($type);
            $this->sessionService->setActiveStatisticsForClownType($currentType);
        } else {
            $currentType = $this->sessionService->getActiveStatisticsForClownType();
        }

        foreach ($clownsWithTotalCount as $k => $clownWithTotalCount) {
            $clownsWithTotalCount[$k]['numerator'] = 0;

            if (StatisticsForClownsType::SUPER === $currentType) {
                foreach ($clownsWithSuperCount as $clownWithSuperCount) {
                    if ($clownWithSuperCount['clown'] === $clownWithTotalCount['clown']) {
                        $clownsWithTotalCount[$k]['numerator'] = $clownWithSuperCount['superCount'];
                    }
                }
                $clownsWithTotalCount[$k]['denominator'] = $clownWithTotalCount['totalCount'];
            } else {
                $availabilites = $clownWithTotalCount['clown']->getClownAvailabilities();
                if ($year) {
                    $startMonth = Month::build($year.'-01');
                    $endMonth = Month::build($year.'-12');
                    $availabilites = $availabilites->filter(
                        fn (ClownAvailability $availability): bool => $availability->getMonth() >= $startMonth && $availability->getMonth() <= $endMonth,
                    );
                }
                $clownsWithTotalCount[$k]['denominator'] = $availabilites->reduce(
                    fn (int $carry, ClownAvailability $availability) => $carry + $availability->{'get'.ucfirst($currentType->value)}(),
                    0,
                );
                $clownsWithTotalCount[$k]['numerator'] = StatisticsForClownsType::SCHEDULED_PLAYS_MONTH === $currentType ? $clownWithTotalCount['totalCount'] :
                    $availabilites->reduce(
                        fn (int $carry, ClownAvailability $availability) => $carry + $availability->getScheduledPlaysMonth(),
                        0,
                    );
            }
        }

        return $this->render('statistics/clown_property_percentage.html.twig', [
            'month' => null,
            'clownsWithCounts' => $clownsWithTotalCount,
            'active' => 'statistics',
            'type' => $currentType,
            'activeYear' => $year,
            'years'      => $years,
            'showYears'  => !is_null($year),
        ]);
    }

    #[Route('/statistics/{monthId}', name: 'statistics', methods: ['GET'])]
    public function showPerMonth(SessionInterface $session, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $schedule = $this->scheduleRepository->find($month) ?? (new Schedule())->setMonth($month);
        $playDates = $this->playDateRepository->regularByMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);
        $substitutionTimeSlots = $this->substitutionRepository->byMonth($month);

        $this->fairPlayCalculator->calculateAvailabilityRatios($clownAvailabilities, $playDates);

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
            'schedule' => $schedule,
            'clownAvailabilities' => $clownAvailabilities,
            'currentPlays' => $plays,
            'currentPlayDatesCount' => count($playDates),
            'currentSubstitutions' => $substitutions,
            'active' => 'statistics',
            'showMaxPerWeek' => $this->configRepository->isFeatureMaxPerWeekActive(),
            'showVenuePreferences' => $this->configRepository->isFeatureClownVenuePreferencesActive(),
            'calculatedRating' => $schedule->getCalculatedRating(),
            'currentRating' => $this->rosterCalculatorGateway->rating($playDates, $clownAvailabilities),
        ]);
    }
}
