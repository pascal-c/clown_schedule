<?php

namespace App\Component\Dashboard;

use App\Entity\Clown;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dashboard/open_invoices_for_clown')]
class OpenInvoicesForClownComponent
{
    public Clown $currentClown;
    public Collection $invoices;

    public function mount(Clown $currentClown)
    {
        $this->currentClown = $currentClown;
        $this->invoices = $currentClown->getInvoices()->filter(fn ($invoice) => $invoice->isOpen());
    }
}
