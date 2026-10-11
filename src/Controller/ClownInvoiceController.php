<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\PlayDate;
use App\Form\InvoiceFormType;
use App\Guard\InvoiceGuard;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use App\Repository\MonthRepository;
use App\Repository\PlayDateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ClownInvoiceController extends AbstractProtectedController
{
    public function __construct(
        private ClownRepository $clownRepository,
        private MonthRepository $monthRepository,
        private PlayDateRepository $playDateRepository,
        private ConfigRepository $configRepository,
        private EntityManagerInterface $entityManager,
        private InvoiceGuard $invoiceGuard,
    ) {
    }

    #[Route('/clowns/{clownId}/invoices/{monthId}', name: 'clown_invoice_show', methods: ['GET'])]
    public function show(SessionInterface $session, int $clownId, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $clown = $this->clownRepository->find($clownId);
        $invoice = $clown->getInvoiceFor($month);
        $playDates = $this->playDateRepository->confirmedAndPaidByMonthAndClown($month, $clown);
        $activeClowns = $this->clownRepository->allActive();
        $invoiceForm = $this->createForm(InvoiceFormType::class, $invoice);

        return $this->render('clown_invoice/show.html.twig', [
            'active' => 'clown_invoice',
            'playDates' => $playDates,
            'clown' => $clown,
            'activeClowns' => $activeClowns,
            'month' => $month,
            'config' => $this->configRepository->find(),
            'feeStandardSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getFee()?->getFeeStandard(),
                    $playDates
                )
            ),
            'feeAlternativeSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getFee()?->getFeeAlternative(),
                    $playDates
                )
            ),
            'kilometersFeeSum' => array_sum(
                array_map(
                    fn (PlayDate $playDate) => $playDate->getFee()?->getKilometersFee(),
                    $playDates
                )
            ),
            'invoice' => $invoice,
            'invoiceForm' => $invoiceForm->createView(),
        ]);
    }

    #[Route('/clowns/{clownId}/invoices/{monthId}', name: 'clown_invoice_set_status', methods: ['POST'])]
    public function setStatus(Request $request, SessionInterface $session, int $clownId, ?string $monthId = null): Response
    {
        $month = $this->monthRepository->find($session, $monthId);
        $clown = $this->clownRepository->find($clownId);
        $invoice = $clown->getInvoiceFor($month);
        $invoiceForm = $this->createForm(InvoiceFormType::class, $invoice);
        $this->checkAuthorization($this->invoiceGuard->canUpdate($invoice));

        $invoiceForm->handleRequest($request);
        if ($invoiceForm->isSubmitted() && $invoiceForm->isValid()) {


            if ($invoiceForm->has('mark_issued') && $invoiceForm->get('mark_issued')?->isClicked()) {
                $invoice->setStatus(Invoice::STATUS_ISSUED);
            } elseif ($invoiceForm->has('mark_paid') && $invoiceForm->get('mark_paid')?->isClicked()) {
                $invoice->setStatus(Invoice::STATUS_PAID);
            } elseif ($invoiceForm->has('mark_open') && $invoiceForm->get('mark_open')?->isClicked()) {
                $invoice->setStatus(Invoice::STATUS_OPEN);
            }
            $this->entityManager->flush();
        }

        return new RedirectResponse($this->generateUrl('clown_invoice_show', ['clownId' => $clownId, 'monthId' => $month->getKey()]));
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['active' => 'play_date']), $response);
    }
}
