<?php

namespace App\Component\Dashboard;

use App\Repository\InvoiceRepository;
use App\Service\ConfigService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dashboard/issued_invoices_for_admin')]
class IssuedInvoicesForAdminComponent
{
    public Collection $invoices;

    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private ConfigService $configService,
    ) {
    }

    public function mount()
    {
        if (!$this->configService->hasFeatureInvoicePaymentClowns()) {
            $this->invoices = new ArrayCollection();

            return;
        }

        $this->invoices = new ArrayCollection($this->invoiceRepository->findIssued());
    }
}
