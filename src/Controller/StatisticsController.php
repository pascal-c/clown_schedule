<?php
namespace App\Controller;

use App\Repository\ClownAvailabilityRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private PlayDateRepository $playDateRepository,
        private MonthRepository $monthRepository)
    {
    }

    #[Route('/statistics/{monthId}', name: 'statistics', methods: ['GET'])]
    public function show(SessionInterface $session, Request $request, ?string $monthId = null): Response 
    {
        $month = $this->monthRepository->find($session, $monthId);
        $playDates = $this->playDateRepository->byMonth($month);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth($month);

        $plays = [];
        foreach ($clownAvailabilities as $availability) { 
            $plays[$availability->getClown()->getId()] = 0;
        }
        foreach($playDates as $playDate) {
            foreach($playDate->getPlayingClowns() as $clown) {
                if (!isset($plays[$clown->getId()])) {
                    $plays[$clown->getId()] = 0;
                }
                $plays[$clown->getId()]++;
            }
        }

        return $this->render('statistics/show.html.twig', [
            'month' => $month,
            'clownAvailabilities' => $clownAvailabilities,
            'plays' => $plays,
            'currentPlayDatesCount' => count($playDates),
            'active' => 'statistics',
        ]);
    }
}
