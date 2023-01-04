<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\Service\Scheduler;
use App\ViewModel\Schedule;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        private Scheduler $scheduler)
    {
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/schedule/{monthId}', name: 'schedule', methods: ['GET'])]
    public function schedule(SessionInterface $session, Request $request, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
    
        $schedule = new Schedule($month);
        foreach ($this->playDateRepository->byMonth($month) as $playDate) {
            $schedule->add($playDate->getDate(), $playDate->getDaytime(), $playDate);
        }

        $form = $this->createFormBuilder()
            ->add('save', SubmitType::class, ['label' => 'Spielplan erstellen', 'attr' => array('onclick' => 'return confirm("Achtung! Alle vorhandenen Zuordnungen werden entfernt!")')])
            ->getForm();

        return $this->renderForm('schedule/show.html.twig', [
            'schedule' => $schedule,
            'month' => $month,
            'form' => $form,
            'showAvailabilities' => $request->query->get('showAvailabilities') == 'yes'
        ]);
    }

    #[Route('/schedule/{monthId}', methods: ['POST'])]
    public function create(SessionInterface $session, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
        $this->scheduler->calculate($month);
        $this->entityManager->flush();

        $this->addFlash('success', 'Yes! Spielplan wurde erstellt. Bitte nochmal prüfen, ob alles so passt!');
        return $this->redirectToRoute('schedule', ['monthId' => $month->getKey()]);
    }

    protected function renderForm(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::renderForm($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
