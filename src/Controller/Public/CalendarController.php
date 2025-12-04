<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Repository\CalendarRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\CalendarExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractController
{
    public function __construct(
        private CalendarExporter $calendarExporter,
        private CalendarRepository $calendarRepository,
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
    ) {
    }

    #[Route('/calendar/subscriptions/{uuid}', name: 'calendar_download_for_subscription', methods: ['GET'])]
    public function downloadIcs(Request $request, string $uuid): Response
    {
        $calendar = $this->calendarRepository->findByUuid($uuid);
        if (is_null($calendar)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($calendar->isFull()) {
            $dates = $this->playDateRepository->all();
            $substitutions = []; // we don'need substitutions, they are shown in the play dates already
            $filename = 'calendar_all.ics';
        } else {
            $clown = $calendar->getClown();
            $dates = $this->playDateRepository->byClown($clown);
            $substitutions = $this->substitutionRepository->byClown($clown);
            $filename = 'calendar_'.$clown->getName().'.ics';
        }
        $icsContent = $this->calendarExporter->ics($dates, $substitutions);

        return new Response($icsContent, Response::HTTP_OK, [
            'Content-type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
