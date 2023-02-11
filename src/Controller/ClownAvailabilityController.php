<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Daytime;
use App\Entity\Month;
use App\Form\ClownAvailabilityFormType;
use App\Repository\ClownRepository;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\ViewController\ScheduleViewController;
use App\ViewModel\Schedule;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ClownAvailabilityController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine, 
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private ClownRepository $clownRepository,
        private MonthRepository $monthRepository,
        private PlayDateRepository $playDateRepository,
        private ScheduleViewController $scheduleViewController
    )
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/clowns/availabilities/{monthId}', name: 'clown_availability_index', methods: ['GET'])]
    public function index(SessionInterface $session, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
        $clowns = $this->clownRepository->all();

        return $this->render('clown_availability/index.html.twig', [
            'active' => 'availability',
            'clowns' => $clowns,
            'month' => $month,
        ]);
    }

    #[Route('/clowns/{clownId}/availabilities/{monthId}', name: 'clown_availability_show', methods: ['GET'])]
    public function show(SessionInterface $session, int $clownId, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
        $clown = $this->clownRepository->find($clownId);
        $clownAvailability = $this->clownAvailabilityRepository->find($month, $clown);
        if (is_null($clownAvailability)) {
            return $this->redirectToRoute('clown_availability_new', ['monthId' => $month->getkey(), 'clownId' => $clown->getId()]);
        }
    
        $schedule = $this->scheduleViewController->getSchedule($month);
        foreach ($clownAvailability->getClownAvailabilityTimes($month) as $timeSlot) {
            $schedule->add($timeSlot->getDate(), $timeSlot->getDaytime(), 'availabilities', $timeSlot->getAvailability());
        }
        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $schedule->add($playDate->getDate(), $playDate->getDaytime(), 'playDates', $playDate);
        }

        return $this->render('clown_availability/show.html.twig', [
            'active' => 'availability',
            'clownAvailability' => $clownAvailability,
            'clown' => $clown,
            'month' => $month,
            'schedule' => $schedule,
        ]);
    }

    #[Route('/clowns/{clownId}/availabilities/{monthId}/new', name: 'clown_availability_new', methods: ['GET'])]
    public function new(int $clownId, string $monthId): Response
    {
        $month = new Month(new \DateTimeImmutable($monthId));
        $clown = $this->clownRepository->find($clownId);
        $schedule = $this->scheduleViewController->getSchedule($month);
        $clownAvailability = new ClownAvailability;
        $lastMonthAvailability = $this->clownAvailabilityRepository->find($month->previous(), $clown);
        if (!is_null($lastMonthAvailability)) {
            $clownAvailability->setWishedPlaysMonth($lastMonthAvailability->getWishedPlaysMonth());
            $clownAvailability->setMaxPlaysMonth($lastMonthAvailability->getMaxPlaysMonth());
            $clownAvailability->setMaxPlaysDay($lastMonthAvailability->getMaxPlaysDay());
        }
        $form = $this->createForm(ClownAvailabilityFormType::class, $clownAvailability);

        foreach($schedule->getDays() as $day) {
            $day->addEntry(Daytime::AM, 'availabilities', 'yes');
            $day->addEntry(Daytime::PM, 'availabilities', 'yes');
        }
        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $schedule->add($playDate->getDate(), $playDate->getDaytime(), 'playDates', $playDate);
        }


        return $this->render('clown_availability/form.html.twig', [
            'active' => 'availability',
            'clown' => $clown,
            'month' => $month,
            'schedule' => $schedule,
            'form' => $form,
            'method' => 'POST',
        ]);
    }

    #[Route('/clowns/{clownId}/availabilities/{monthId}/new', methods: ['POST'])]
    public function create(Request $request, int $clownId, string $monthId): Response
    {
        $month = new Month(new \DateTimeImmutable($monthId));
        $clown = $this->clownRepository->find($clownId);
        $clownAvailability = new ClownAvailability;
        $clownAvailability->setMonth($month);
        $clownAvailability->setClown($clown);

        $form = $this->createForm(ClownAvailabilityFormType::class, $clownAvailability);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $availabilities = $request->request->all()['availability'];
            foreach($availabilities as $date => $timeSlots) {
                foreach ($timeSlots as $daytime => $availability) {
                    $timeSlot = new ClownAvailabilityTime;
                    $timeSlot->setClown($clown);
                    $timeSlot->setDate(new \DateTimeImmutable($date));
                    $timeSlot->setDaytime($daytime);
                    $timeSlot->setAvailability($availability);
                    $clownAvailability->addClownAvailabilityTime($timeSlot);
                    $this->entityManager->persist($timeSlot);
                }
            }
            $this->entityManager->persist($clownAvailability);
            $this->entityManager->flush();
            $this->addFlash('success', 'Fehlzeiten wurden gespeichert. Vielen Dank!');
            return $this->redirectToRoute('clown_availability_show', ['clownId' => $clown->getId()]);
        }

        $this->addFlash('warning', 'Speichern fehlgeschlagen! Bitte nochmal versuchen!');
        return $this->redirectToRoute('clown_availability_new', ['clownId' => $clown->getId(), 'monthId' => $month->getKey()]);
    }

    #[Route('/clowns/{clownId}/availabilities/{monthId}/edit', name: 'clown_availability_edit', methods: ['GET'])]
    public function edit(int $clownId, string $monthId): Response
    {
        $month = new Month(new \DateTimeImmutable($monthId));
        $clown = $this->clownRepository->find($clownId);
        $clownAvailability = $this->clownAvailabilityRepository->find($month, $clown);
        $schedule = $this->scheduleViewController->getSchedule($month);
        $form = $this->createForm(ClownAvailabilityFormType::class, $clownAvailability, ['method' => 'PATCH']);

        foreach ($clownAvailability->getClownAvailabilityTimes() as $timeSlot) {
            $schedule->add($timeSlot->getDate(), $timeSlot->getDaytime(), 'availabilities', $timeSlot->getAvailability());
        }
        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $schedule->add($playDate->getDate(), $playDate->getDaytime(), 'playDates', $playDate);
        }

        return $this->render('clown_availability/form.html.twig', [
            'active' => 'availability',
            'clown' => $clown,
            'month' => $month,
            'schedule' => $schedule,
            'form' => $form,
        ]);
    }

    #[Route('/clowns/{clownId}/availabilities/{monthId}/edit', methods: ['PATCH'])]
    public function update(Request $request, int $clownId, string $monthId): Response
    {
        $month = new Month(new \DateTimeImmutable($monthId));
        $clown = $this->clownRepository->find($clownId);
        $clownAvailability = $this->clownAvailabilityRepository->find($month, $clown);
        $form = $this->createForm(ClownAvailabilityFormType::class, $clownAvailability, ['method' => 'PATCH']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $availabilities = $request->request->all()['availability'];
            foreach ($clownAvailability->getClownAvailabilityTimes() as $timeSlot) {
                $date = $timeSlot->getDate()->format('Y-m-d');
                $timeSlot->setAvailability($availabilities[$date][$timeSlot->getDaytime()]);
            }
            $this->entityManager->flush();
            $this->addFlash('success', 'Fehlzeiten wurden geändert. Gut!');
            return $this->redirectToRoute('clown_availability_show', ['clownId' => $clown->getId()]);
        }

        $this->addFlash('warning', 'Speichern fehlgeschlagen! Bitte nochmal versuchen!');
        return $this->redirectToRoute('clown_availability_edit', ['clownId' => $clown->getId(), 'monthId' => $month->getKey()]);
    }
}
