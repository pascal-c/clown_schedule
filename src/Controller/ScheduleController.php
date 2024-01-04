<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Month;
use App\Entity\Schedule;
use App\Form\ScheduleCalculateFormType;
use App\Form\ScheduleCompleteFormType;
use App\Mailer\ClownInfoMailer;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\ClownRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler;
use App\Service\TimeService;
use App\Value\ScheduleStatus;
use App\ViewController\ScheduleViewController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        private PlayDateRepository $playDateRepository,
        private MonthRepository $monthRepository,
        private ScheduleViewController $scheduleViewController,
        private Scheduler $scheduler,
        private ScheduleRepository $scheduleRepository,
        private ClownRepository $clownRepository,
        private ClownInfoMailer $clownInfoMailer,
        private TimeService $timeService,
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private SubstitutionRepository $substitutionRepository,
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/schedule/{monthId}', name: 'schedule', methods: ['GET'])]
    public function schedule(SessionInterface $session, Request $request, string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $scheduleViewModel = $this->scheduleViewController->getSchedule($month);

        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $scheduleViewModel->add($playDate, 'playDates', $playDate);
        }

        $calculateForm = $this->createForm(ScheduleCalculateFormType::class, $this->scheduleRepository->find($month));
        $completeForm = $this->createForm(ScheduleCompleteFormType::class, $this->scheduleRepository->find($month));

        return $this->render('schedule/show.html.twig', [
            'schedule' => $scheduleViewModel,
            'month' => $month,
            'calculateForm' => $calculateForm,
            'completeForm' => $completeForm,
        ]);
    }

    #[Route('/schedule/{monthId}', methods: ['POST'])]
    public function calculate(Request $request, SessionInterface $session, string $monthId = null): Response
    {
        $this->adminOnly();

        $month = $this->monthRepository->find($session, $monthId);
        $schedule = $this->scheduleRepository->find($month);

        if (null === $schedule) {
            $schedule ??= (new Schedule())->setMonth($month)->setStatus(ScheduleStatus::IN_PROGRESS);
            $this->entityManager->persist($schedule);
        }
        if (ScheduleStatus::COMPLETED === $schedule->getStatus()) {
            throw $this->createAccessDeniedException('Spielplan ist bereits abgeschlossen!');
        }

        $this->scheduler->calculate($month);
        $this->entityManager->flush();

        $this->addFlash('success', 'Yes! Spielplan wurde erstellt. Bitte nochmal prüfen, ob alles so passt!');

        return $this->redirectToRoute('schedule', ['monthId' => $month->getKey()]);
    }

    #[Route('/schedule/{monthId}', methods: ['PUT'])]
    public function complete(SessionInterface $session, string $monthId = null): Response
    {
        $this->adminOnly();

        $month = $this->monthRepository->find($session, $monthId);
        $schedule = $this->scheduler->complete($month);
        if (!$schedule) {
            throw $this->createAccessDeniedException('Der Spielplan ist bereits abgeschlossen!');
        }

        $this->entityManager->flush();

        // send Email to active clowns, but only if Month not in the past
        if (Month::build('now')->getKey() <= $schedule->getMonth()->getKey()) {
            foreach ($this->clownRepository->allActive() as $clown) {
                $this->clownInfoMailer->sendScheduleCompletedMail($clown, $schedule);
            }
        }

        $this->addFlash('success', 'Cool, Spielplan ist abgeschlossen und ist nun für alle sichtbar. Manuelle Änderungen können trotzdem noch vorgenommen werden.');

        return $this->redirectToRoute('schedule', ['monthId' => $month->getKey()]);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
