<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\VenueRepository;
use App\Service\SessionService;
use App\Service\TimeService;
use App\Value\PlayDateType;
use App\Value\StatisticsForVenuesType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsPerVenueController extends AbstractProtectedController
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private VenueRepository $venueRepository,
        private TimeService $timeService,
        private SessionService $sessionService,
        private ConfigRepository $configRepository,
    ) {
    }

    #[Route('/statistics/per-venue/infinity', name: 'statistics_per_venue_infinity', methods: ['GET'])]
    public function showInfinity(#[MapQueryParameter] ?string $type = null): Response
    {
        if ($type) {
            $currentType = StatisticsForVenuesType::from($type);
            $this->sessionService->setActiveStatisticsForVenueType($currentType);
        } else {
            $currentType = $this->sessionService->getActiveStatisticsForVenueType();
        }

        return match($currentType) {
            StatisticsForVenuesType::BY_TYPE => $this->showPerType(null),
            StatisticsForVenuesType::BY_STATUS => $this->showPerStatus(null),
            StatisticsForVenuesType::WITH_FEE => $this->showWithFee(null),
        };
    }

    #[Route('/statistics/per-venue/per-year/{year}', name: 'statistics_per_venue_per_year', methods: ['GET'])]
    public function showPerYear(?string $year = null, #[MapQueryParameter] ?string $type = null): Response
    {
        if ($type) {
            $currentType = StatisticsForVenuesType::from($type);
            $this->sessionService->setActiveStatisticsForVenueType($currentType);
        } else {
            $currentType = $this->sessionService->getActiveStatisticsForVenueType();
        }

        $year ??= $this->timeService->currentYear();

        return match($currentType) {
            StatisticsForVenuesType::BY_TYPE => $this->showPerType($year),
            StatisticsForVenuesType::BY_STATUS => $this->showPerStatus($year),
            StatisticsForVenuesType::WITH_FEE => $this->showWithFee($year),
        };
    }

    private function showPerType(?string $year): Response
    {
        $venues = array_map(
            fn ($venue) => [
                'name' => $venue->getName(),
                'totalCount' => $venue->getPlayDates()->count(),
                'regularCount' => $venue->getPlayDates()->filter(
                    fn (PlayDate $playDate) => PlayDateType::REGULAR === $playDate->getType(),
                )->count(),
                'specialCount' => $venue->getPlayDates()->filter(
                    fn (PlayDate $playDate) => PlayDateType::SPECIAL === $playDate->getType(),
                )->count(),
            ],
            $this->venueRepository->allWithPlays($year, onlyConfirmed: true),
        );

        $playDatesWithoutVenue = $this->playDateRepository->withoutVenue($year, onlyConfirmed: true);
        if (count($playDatesWithoutVenue) > 0) {
            $venues[] = [
                'name' => 'Ohne Veranstaltungsort',
                'totalCount' => count($playDatesWithoutVenue),
                'regularCount' => count(array_filter(
                    $playDatesWithoutVenue,
                    fn (PlayDate $playDate) => PlayDateType::REGULAR === $playDate->getType(),
                )),
                'specialCount' => count(array_filter(
                    $playDatesWithoutVenue,
                    fn (PlayDate $playDate) => PlayDateType::SPECIAL === $playDate->getType(),
                )),
            ];
        }

        return $this->render('statistics/per_venue_per_type.html.twig', [
            'venues' => $venues,
            'activeYear' => $year,
            'showYears'  => !is_null($year),
            'type'       => StatisticsForVenuesType::BY_TYPE,
        ]);
    }

    private function showPerStatus(?string $year): Response
    {
        $venues = array_map(
            fn ($venue) => [
                'name' => $venue->getName(),
                'totalCount' => $venue->getPlayDates()->count(),
                'cancelledCount' => $venue->getPlayDates()->filter(
                    fn (PlayDate $playDate) => PlayDate::STATUS_CANCELLED === $playDate->getStatus(),
                )->count(),
                'movedCount' => $venue->getPlayDates()->filter(
                    fn (PlayDate $playDate) => PlayDate::STATUS_MOVED === $playDate->getStatus(),
                )->count(),
            ],
            $this->venueRepository->allWithPlays($year),
        );

        $playDatesWithoutVenue = $this->playDateRepository->withoutVenue($year);
        if (count($playDatesWithoutVenue) > 0) {
            $venues[] = [
                'name' => 'Ohne Veranstaltungsort',
                'totalCount' => count($playDatesWithoutVenue),
                'cancelledCount' => count(array_filter(
                    $playDatesWithoutVenue,
                    fn (PlayDate $playDate) => PlayDate::STATUS_CANCELLED === $playDate->getStatus(),
                )),
                'movedCount' => count(array_filter(
                    $playDatesWithoutVenue,
                    fn (PlayDate $playDate) => PlayDate::STATUS_MOVED === $playDate->getStatus(),
                )),
            ];
        }

        return $this->render('statistics/per_venue_per_status.html.twig', [
            'venues' => $venues,
            'activeYear' => $year,
            'showYears'  => !is_null($year),
            'type'       => StatisticsForVenuesType::BY_STATUS,
        ]);
    }

    private function showWithFee(?string $year): Response
    {
        $venues = array_map(
            fn ($venue) => [
                'name' => $venue->getName(),
                'totalCount' => $venue->getPlayDates()->count(),
                'feeStandard' => $venue->getPlayDates()->reduce(
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()->getFeeStandard() * $playDate->getPlayingClowns()->count(),
                    0.0,
                ),
                'feeAlternative' => $venue->getPlayDates()->reduce(
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()->getFeeAlternative() * $playDate->getPlayingClowns()->count(),
                    0.0,
                ),
                'feePerKilometer' => $venue->getPlayDates()->reduce(
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()->getKilometersFee() * ($playDate->getFee()?->isKilometersFeeForAllClowns() ? $playDate->getPlayingClowns()->count() : 1),
                    0.0,
                ),
            ],
            $this->venueRepository->allWithPlays($year, onlyConfirmed: true),
        );

        $playDatesWithoutVenue = $this->playDateRepository->withoutVenue($year, onlyConfirmed: true);
        if (count($playDatesWithoutVenue) > 0) {
            $venues[] = [
                'name' => 'Ohne Veranstaltungsort',
                'totalCount' => count($playDatesWithoutVenue),
                'feeStandard' => array_reduce(
                    $playDatesWithoutVenue,
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()?->getFeeStandard() * $playDate->getPlayingClowns()->count(),
                    0.0,
                ),
                'feeAlternative' => array_reduce(
                    $playDatesWithoutVenue,
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()?->getFeeAlternative() * $playDate->getPlayingClowns()->count(),
                    0.0,
                ),
                'feePerKilometer' => array_reduce(
                    $playDatesWithoutVenue,
                    fn ($fee, PlayDate $playDate) => $fee + $playDate->getFee()?->getKilometersFee() * ($playDate->getFee()?->isKilometersFeeForAllClowns() ? $playDate->getPlayingClowns()->count() : 1),
                    0.0,
                ),
            ];
        }

        return $this->render('statistics/per_venue_with_fee.html.twig', [
            'venues' => $venues,
            'activeYear' => $year,
            'showYears'  => !is_null($year),
            'type'       => StatisticsForVenuesType::WITH_FEE,
            'config'     => $this->configRepository->find(),
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $years = range($this->playDateRepository->minYear(), $this->playDateRepository->maxYear());

        return parent::render(
            $view,
            array_merge($parameters, [
                'active' => 'statistics',
                'years'      => $years,
            ]),
            $response
        );
    }
}
