<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;
use App\Repository\VenueRepository;
use App\Service\SessionService;
use App\Service\TimeService;
use App\Value\PlayDateType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsPerVenueController extends AbstractProtectedController
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private VenueRepository $venueRepository,
        private TimeService $timeService,
        private SessionService $sessionService,
    ) {
    }

    #[Route('/statistics/per-venue/infinity', name: 'statistics_per_venue_infinity', methods: ['GET'])]
    public function showInfinity(#[MapQueryParameter] ?string $type = null): Response
    {
        return $this->showPerVenue($type, null);
    }

    #[Route('/statistics/per-venue/per-year/{year}', name: 'statistics_per_venue_per_year', methods: ['GET'])]
    public function showPerYear(?string $year = null, #[MapQueryParameter] ?string $type = null): Response
    {
        $year ??= $this->timeService->currentYear();

        return $this->showPerVenue($type, $year);
    }

    private function showPerVenue(?string $type, ?string $year): Response
    {
        $years = range($this->playDateRepository->minYear(), $this->playDateRepository->maxYear());
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
            $this->venueRepository->allWithConfirmedPlayDates($year),
        );

        $playDatesWithoutVenue = $this->playDateRepository->confirmedWithoutVenue($year);
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

        return $this->render('statistics/per_venue.html.twig', [
            'venues' => $venues,
            'active' => 'statistics',
            'activeYear' => $year,
            'years'      => $years,
            'showYears'  => !is_null($year),
        ]);
    }
}
