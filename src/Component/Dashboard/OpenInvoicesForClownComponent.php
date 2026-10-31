<?php

namespace App\Component\Dashboard;

use App\Entity\Clown;
use App\Service\ConfigService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dashboard/open_invoices_for_clown')]
class OpenInvoicesForClownComponent
{
    public Clown $currentClown;
    public Collection $invoices;

    public function __construct(private ConfigService $configService)
    {
    }

    public function mount(Clown $currentClown): void
    {
        if (!$this->configService->hasFeatureInvoicingClowns() || !$currentClown->getSettings()->openInvoiceOnDashboard) {
            $this->invoices = new ArrayCollection();

            return;
        }

        $this->currentClown = $currentClown;
        $this->invoices = $currentClown->getInvoices()->filter(fn ($invoice) => $invoice->isOpen());
    }
}
