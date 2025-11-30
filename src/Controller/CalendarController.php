<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\CalendarExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractProtectedController
{
    public function __construct(
        private CalendarExporter $calendarExporter,
        private MonthRepository $monthRepository,
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
    ) {
    }

    #[Route('/calendar/export/download/{monthId}', name: 'calendar_download', methods: ['GET'])]
    public function downloadIcs(Request $request, ?string $monthId = null, #[MapQueryParameter] ?string $type = null): Response
    {
        $month = $this->monthRepository->find($request->getSession(), $monthId);
        if ('all' === $type) {
            $dates = $this->playDateRepository->byMonth($month);
            $substitutions = []; // we don'need substitutions, they are shown in the play dates already
            $filename = 'calendar_'.$month->getKey().'_all.ics';
        } else {
            $clown = $this->authService->getCurrentClown();
            $dates = $this->playDateRepository->byMonthAndClown($month, $clown);
            $substitutions = $this->substitutionRepository->byMonthAndClown($month, $clown);
            $filename = 'calendar_'.$month->getKey().'_'.$clown->getName().'.ics';
        }
        $icsContent = $this->calendarExporter->ics($dates, $substitutions);

        return new Response($icsContent, Response::HTTP_OK, [
            'Content-type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    #[Route('/calendar/export/form/{monthId}', name: 'calendar_export', methods: ['GET'])]
    public function form(Request $request, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($request->getSession(), $monthId);

        return $this->render('calendar/form.html.twig', [
            'month' => $month,
            'active' => 'play_date',
        ]);
    }
}
