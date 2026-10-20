<?php

namespace App\Component\Dashboard;

use App\Entity\Month;
use App\Repository\InvoiceRepository;
use App\Service\ConfigService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dashboard/open_invoices_for_admin')]
class OpenInvoicesForAdminComponent
{
    public Collection $invoices;
    public Month $openOverDueMonth;

    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private ConfigService $configService,
    ) {
    }

    public function mount()
    {
        if (!$this->configService->hasFeatureInvoicingClowns()) {
            $this->invoices = new ArrayCollection();

            return;
        }
        $this->invoices = new ArrayCollection($this->invoiceRepository->findOpenOverDue());
        $this->openOverDueMonth = $this->invoiceRepository->getOpenOverDueMonth();
    }
}
