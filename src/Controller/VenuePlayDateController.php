<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Service\TimeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class VenuePlayDateController extends AbstractProtectedController
{
    public function __construct(private TimeService $timeService)
    {
    }

    #[Route('/venues/{id}/play_dates', name: 'venue_play_date_index', methods: ['GET'])]
    public function index(Venue $venue, #[MapQueryParameter] ?string $year = null): Response
    {
        $year ??= $this->timeService->currentYear();
        $playDates = $venue->getPlayDates();
        $years = array_unique($playDates->map(fn (PlayDate $playDate): string => $playDate->getDate()->format('Y'))->toArray());

        return $this->render('venue/play_date/index.html.twig', [
            'venue' => $venue,
            'active' => 'venue',
            'activeYear' => $year,
            'years' => $years,
            'playDates' => $venue->getPlayDates()->filter(
                fn (PlayDate $playDate): bool => $year === $playDate->getDate()->format('Y')
            ),
        ]);
    }
}
