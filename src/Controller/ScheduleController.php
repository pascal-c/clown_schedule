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
use App\Repository\ConfigRepository;
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
        private ConfigRepository $configRepository,
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/schedule/calculate/{monthId}', name: 'calculate', methods: ['GET'])]
    public function calculateForm(SessionInterface $session, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $schedule = $this->scheduleRepository->find($month) ?? (new Schedule())->setMonth($month)->setStatus(ScheduleStatus::NOT_STARTED);

        $calculateForm = $this->createForm(ScheduleCalculateFormType::class, $schedule);
        $completeForm = $this->createForm(ScheduleCompleteFormType::class, $schedule, [
            'action' => $this->generateUrl('schedule'),
        ]);

        return $this->render('schedule/calculate.html.twig', [
            'schedule' => $schedule,
            'month' => $month,
            'calculateForm' => $calculateForm,
            'completeForm' => $completeForm,
        ]);
    }

    #[Route('/schedule/calculate/{monthId}', methods: ['POST'])]
    public function calculate(Request $request, SessionInterface $session, ?string $monthId = null): Response
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

        $calculateForm = $this->createForm(ScheduleCalculateFormType::class, $schedule);
        $calculateForm->handleRequest($request);
        if (!$calculateForm->isSubmitted() || !$calculateForm->isValid()) {
            $this->addFlash('danger', 'Ui! Das hat irgendwie überhaupt nicht geklappt! Tut mir sehr leid! Bitte versuche es einfach nochmal.');

            return $this->redirectToRoute('schedule', ['monthId' => $month->getKey()]);
        }

        $start = microtime(true);
        $result = $this->scheduler->calculate($month, 'calculate_complex' === $calculateForm->getClickedButton()->getName());
        $seconds = number_format(microtime(true) - $start, 1, ',', '.');

        if ($result->success) {
            $this->entityManager->flush();
            $message = new \Twig\Markup(
                $this->renderView('flashes/schedule_calculated_successfully.html.twig', ['seconds' => $seconds, 'result' => $result]),
                'UTF-8'
            );
            $this->addFlash('success', $message);
        } else {
            $message = new \Twig\Markup(
                $this->renderView('flashes/schedule_calculated_failure.html.twig', ['seconds' => $seconds, 'result' => $result]),
                'UTF-8'
            );
            $this->addFlash('danger', $message);
        }

        return $this->redirectToRoute('schedule', ['monthId' => $month->getKey()]);
    }

    #[Route('/schedule/{monthId}', name: 'schedule', methods: ['GET'])]
    public function show(SessionInterface $session, Request $request, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $scheduleViewModel = $this->scheduleViewController->getSchedule($month);

        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $scheduleViewModel->add($playDate, 'playDates', $playDate);
        }

        return $this->render('schedule/show.html.twig', [
            'schedule' => $scheduleViewModel,
            'month' => $month,
            'showAvailableClowns' => $this->configRepository->isFeatureCalculationActive(),
        ]);
    }

    #[Route('/schedule/{monthId}', methods: ['PUT'])]
    public function complete(SessionInterface $session, ?string $monthId = null): Response
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

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
