<?php

namespace App\Component\ClownInvoice;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use App\Form\InvoiceFormType;
use App\Service\ConfigService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('clown_invoice/status')]
class StatusComponent
{
    public ?Invoice $invoice = null;
    public FormView $invoiceForm;
    public bool $showPayment;

    public function __construct(private FormFactoryInterface $formFactory, private ConfigService $configService)
    {
    }

    public function mount(Month $month, Clown $clown): void
    {
        if (!$this->configService->hasFeatureInvoicingClowns()) {
            return;
        }

        $this->showPayment = $this->configService->hasFeatureInvoicePaymentClowns();
        $this->invoice = $clown->getInvoiceFor($month);
        $this->invoiceForm = $this->formFactory
            ->create(InvoiceFormType::class, $this->invoice)
            ->createView();
    }
}
