<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Calendar;
use App\Repository\CalendarRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\CalendarExporter;
use App\Value\CalendarType;
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
        private EntityManagerInterface $entityManager,
        private CalendarRepository $calendarRepository,
    ) {
    }

    #[Route('/calendar/download/{monthId}', name: 'calendar_download', methods: ['GET'])]
    public function downloadIcs(Request $request, ?string $monthId = null, #[MapQueryParameter] ?string $type = null): Response
    {
        $month = $this->monthRepository->find($request->getSession(), $monthId);
        if (CalendarType::ALL->value === $type) {
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
        $token = Uuid::uuid4()->toString();
        $request->getSession()->set('calendar-token', $token);

        return $this->render('calendar/form.html.twig', [
            'month' => $month,
            'token' => $token,
            'types' => CalendarType::cases(),
            'active' => 'play_date',
        ]);
    }

    #[Route('/calendar/subscription', name: 'calendar_create_subscription', methods: ['POST'])]
    public function createCalendarSubscription(Request $request): Response
    {
        $clown = $this->getCurrentClown();
        $type = CalendarType::from($request->request->get('type'));
        if ($clown->getCalendar($type)) {
            $this->addFlash('warning', 'Es existiert bereits ein Kalender-Link für '.$type);
        } elseif ($request->request->get('token') !== $request->getSession()->get('calendar-token')) {
            $this->addFlash('warning', 'CSRF token ist abgelaufen. Bitte nochmal probieren!');
        } else {
            $this->addFlash('success', 'Ok, Kalender-Link wurde angelegt!');
            $calendar = new Calendar();
            $calendar->setType($type);
            $calendar->setUuid(Uuid::uuid4()->toString());
            $clown->addCalendar($calendar);
            $this->entityManager->persist($calendar);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('calendar_export');
    }

    #[Route('/calendar/subscription/{uuid}', name: 'calendar_delete_subscription', methods: ['DELETE'])]
    public function deleteCalendarSubscription(Request $request, string $uuid): Response
    {
        $calendar = $this->calendarRepository->findByUuid($uuid);
        if (!$calendar) {
            $this->addFlash('warning', 'Kalendar-Abonnement-Link nicht gefunden. Wurde vielleicht schon gelöscht?');
        } elseif ($this->getCurrentClown() !== $calendar->getClown()) {
            $this->addFlash('warning', 'Schweinebacke! Das ist nicht Dein eigener Kalender. Den kannst Du nicht löschen!');
        } elseif ($request->getSession()->get('calendar-token') !== $request->request->get('token')) {
            $this->addFlash('warning', 'CSRF token ist abgelaufen. Bitte nochmal probieren!');
        } else {
            $this->entityManager->remove($calendar);
            $this->entityManager->flush();

            $this->addFlash('success', 'Kalendar-Abonnement-Link erfolgreich gelöscht.');
        }

        return $this->redirectToRoute('calendar_export');
    }
}
