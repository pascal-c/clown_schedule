<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Calendar;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\CalendarExporter;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
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
        private EntityManagerInterface $entitityManager,
    ) {
    }

    #[Route('/calendar/download/{monthId}', name: 'calendar_download', methods: ['GET'])]
    public function downloadIcs(Request $request, ?string $monthId = null, #[MapQueryParameter] ?string $type = null): Response
    {
        $month = $this->monthRepository->find($request->getSession(), $monthId);
        if (Calendar::TYPE_ALL === $type) {
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

    #[Route('/calendar/form/{monthId}', name: 'calendar_export', methods: ['GET'])]
    public function form(Request $request, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($request->getSession(), $monthId);

        return $this->render('calendar/form.html.twig', [
            'month' => $month,
            'active' => 'play_date',
        ]);
    }

    #[Route('/calendar/create_link', name: 'calendar_create_link', methods: ['POST'])]
    public function createCalendarLink(Request $request): Response
    {
        $clown = $this->getCurrentClown();
        $type = $request->request->get('type');
        if ($clown->getCalendar($type)) {
            $this->addFlash('success', 'Es existiert bereits ein Kalender-Link für ' . $type);
        } else {
            $this->addFlash('success', 'Ok, Kalender-Link wurde angelegt!');
            $calendar = new Calendar();
            $calendar->setType($type);
            $calendar->setUuid(Uuid::uuid4()->toString());
            $clown->addCalendar($calendar);
            $this->entitityManager->persist($calendar);
            $this->entitityManager->flush();            
        }
        return $this->redirectToRoute('calendar_export');
    }
}
