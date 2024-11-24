<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PlayDate;
use App\Repository\ClownRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use App\ViewController\ScheduleViewController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ClownInvoiceController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository,
        private MonthRepository $monthRepository,
        private PlayDateRepository $playDateRepository,
        private ScheduleViewController $scheduleViewController,
    ) {
    }

    #[Route('/clowns/{clownId}/invoices/{monthId}', name: 'clown_invoice_show', methods: ['GET'])]
    public function show(SessionInterface $session, int $clownId, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $clown = $this->clownRepository->find($clownId);
        $playDates = $this->playDateRepository->byMonthAndClown($month, $clown);
        $playDates = array_filter($playDates, fn (PlayDate $playDate): bool => !$playDate->isTraining());
        $regularPlayDates = array_filter($playDates, fn (PlayDate $playDate): bool => $playDate->isRegular());
        $activeClowns = $this->clownRepository->allActive();

        return $this->render('clown_invoice/show.html.twig', [
            'active' => 'clown_invoice',
            'playDates' => $playDates,
            'clown' => $clown,
            'activeClowns' => $activeClowns,
            'month' => $month,
            'feeByPublicTransportSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getVenue()->getFeeByPublicTransport(),
                    $regularPlayDates
                )
            ),
            'feeByCarSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getVenue()->getFeeByCar(),
                    $regularPlayDates
                )
            ),
            'kilometersFeeSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getVenue()->getKilometersFee(),
                    $regularPlayDates
                )
            ),
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
