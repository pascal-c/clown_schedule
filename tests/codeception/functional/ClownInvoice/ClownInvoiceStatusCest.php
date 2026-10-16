<?php

use App\Entity\Invoice;
use App\Entity\Month;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;

class ClownInvoiceStatusCest extends AbstractCest
{
    public function InvoiceWorkflowForClown(FunctionalTester $I): void
    {
        $this->configFactory->update(featureInvoicingClowns: true);

        // Log in as a clown without an open invoice.
        $I->loginAsClown('Emil');
        $I->dontSee('Rechnung stellen');

        // Create an open invoice for the current clown.
        $currentClown = $I->getCurrentUser();
        $this->invoiceFactory->create(clown: $currentClown, month: Month::build('2024-01'), status: Invoice::STATUS_OPEN);
        $I->click('Dashboard');
        $I->see('Rechnung stellen', 'h4');

        // Click the link to view the invoice.
        $I->click('Rechnung für Jan. 2024');
        $I->see('Rechnungsansicht Emil Jan. 2024', 'h4');
        $I->see('Rechnung offen', 'span.badge');

        // mark the invoice as issued.
        $I->click('> Rechnung als gestellt markieren');
        $I->see('Rechnung gestellt', 'span.badge');

        // mark the invoice as open .
        $I->click('< Rechnung als offen markieren');
        $I->see('Rechnung offen', 'span.badge');

        // mark the invoice as issued again
        $I->click('> Rechnung als gestellt markieren');
        $I->see('Rechnung gestellt', 'span.badge');

        // Verify it is no longer on the dashboard
        $I->click('Dashboard');
        $I->dontSee('Rechnung stellen');
    }

    public function withDeactivatedInvoicingForClowns(FunctionalTester $I): void
    {
        $this->configFactory->update(featureInvoicingClowns: false);

        $I->loginAsClown('Emil');

        // Create an open invoice for the current clown.
        $currentClown = $I->getCurrentUser();
        $this->invoiceFactory->create(clown: $currentClown, month: Month::build('2024-01'), status: Invoice::STATUS_OPEN);

        // I dont see open invoices in the dashboard
        $I->click('Dashboard');
        $I->dontSee('Rechnung stellen');

        // I dont see the status in the invoice view
        $I->click('Spielplan', 'nav');
        $I->click('Rechnungsansicht', 'nav');
        $I->dontSee('Rechnungsstatus');
    }

    public function InvoiceWorkflowForAdmin(AdminTester $I): void
    {
        $this->configFactory->update(featureInvoicingClowns: true, featureInvoicePaymentClowns: true);
        Functional::$now = '2024-03-01';

        // Log in as admin without open or issued invoices
        $I->loginAsAdmin();
        $I->click('Büro-Dashboard');
        $I->dontSee('Zu stellende Rechnungen');
        $I->dontSee('Zu bezahlende Rechnungen');

        // Create an open overdued invoice
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Tailor'),
            month: Month::build('2024-01'),
            status: Invoice::STATUS_OPEN
        );
        // Create an open invoice, not overdued yet
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Swift'),
            month: Month::build('2024-02'),
            status: Invoice::STATUS_OPEN
        );

        $I->click('Büro-Dashboard');

        $this->workflowForAdminForOpenInvoices($I);
        $this->workflowForAdminForIssuedInvoices($I);
    }

    private function workflowForAdminForOpenInvoices(AdminTester $I): void
    {
        $I->see('Zu stellende Rechnungen bis Jan. 2024', 'h4');
        $I->see('Tailor: Rechnung für Jan. 2024');
        $I->dontSee('Swift: Rechnung für Feb. 2024');

        // Click the link to view the open invoice.
        $I->click('Tailor: Rechnung für Jan. 2024');
        $I->see('Rechnungsansicht Tailor Jan. 2024', 'h4');
        $I->see('Rechnung offen', 'span.badge');

        // mark the invoice as issued.
        $I->click('> Rechnung als gestellt markieren');
        $I->see('Rechnung gestellt', 'span.badge');
        $I->see('Rechnung offen', 'span'); // this status is visible but not active
        $I->see('Rechnung bezahlt', 'span'); // this status is visible but not active

        // Verify it is no longer in the open invoices list on the dashboard
        $I->click('Büro-Dashboard');
        $I->dontSee('Zu stellende Rechnungen');
    }

    private function workflowForAdminForIssuedInvoices(AdminTester $I): void
    {
        $I->see('Zu bezahlende Rechnungen', 'h4');
        $I->see('Tailor: Rechnung für Jan. 2024');

        // Click the link to view the issued invoice.
        $I->click('Tailor: Rechnung für Jan. 2024');
        $I->see('Rechnungsansicht Tailor Jan. 2024', 'h4');
        $I->see('Rechnung gestellt', 'span.badge');

        // mark the invoice as paid.
        $I->click('> Rechnung als bezahlt markieren');
        $I->see('Rechnung bezahlt', 'span.badge');

        // mark the invoice as issued
        $I->click('< Rechnung als doch nicht bezahlt markieren');
        $I->see('Rechnung gestellt', 'span.badge');

        // mark the invoice as paid again.
        $I->click('> Rechnung als bezahlt markieren');
        $I->see('Rechnung bezahlt', 'span.badge');

        // Verify it is no longer in the issued invoices list on the dashboard
        $I->click('Büro-Dashboard');
        $I->dontSee('Zu bezahlende Rechnungen');
    }

    public function withDeactivatedInvoicingForAdmin(AdminTester $I): void
    {
        $this->configFactory->update(featureInvoicingClowns: false);

        $I->loginAsAdmin();

        // Create an open overdued invoice
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Tailor'),
            month: Month::build('2024-01'),
            status: Invoice::STATUS_OPEN
        );
        // Create an issued invoice
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Swift'),
            status: Invoice::STATUS_PAID
        );

        // I dont see open invoices in the dashboard
        $I->click('Büro-Dashboard');
        $I->dontSee('Zu stellende Rechnungen');
        $I->dontSee('Zu bezahlende Rechnungen');

        // I dont see the status in the invoice view
        $I->click('Spielplan', 'nav');
        $I->click('Rechnungsansicht', 'nav');
        $I->click('Jan. 2024');
        $I->click('Tailor');
        $I->dontSee('Rechnungsstatus');
    }

    public function withDeactivatedPaymentForAdmin(AdminTester $I): void
    {
        $this->configFactory->update(featureInvoicingClowns: true, featureInvoicePaymentClowns: false);

        Functional::$now = '2024-03-01';
        $I->loginAsAdmin();

        // Create an open overdued invoice
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Tailor'),
            month: Month::build('2024-01'),
            status: Invoice::STATUS_OPEN
        );
        // Create an issued invoice
        $this->invoiceFactory->create(
            clown: $this->clownFactory->create(name: 'Swift'),
            status: Invoice::STATUS_PAID
        );

        $I->click('Büro-Dashboard');

        $I->see('Zu stellende Rechnungen');
        $I->dontSee('Zu bezahlende Rechnungen');

        $I->see('Zu stellende Rechnungen bis Jan. 2024', 'h4');
        $I->see('Tailor: Rechnung für Jan. 2024');
        $I->dontSee('Swift');

        // Click the link to view the open invoice.
        $I->click('Tailor: Rechnung für Jan. 2024');
        $I->see('Rechnungsansicht Tailor Jan. 2024', 'h4');
        $I->see('Rechnung offen', 'span.badge');
        $I->see('Rechnung gestellt', 'span'); // this status is visible but not active
        $I->dontSee('Rechnung bezahlt', 'span'); // this status is invisible

        // mark the invoice as issued.
        $I->click('> Rechnung als gestellt markieren');
        $I->see('Rechnung gestellt', 'span.badge');
        $I->see('Rechnung offen', 'span'); // this status is visible but not active
        $I->dontSee('Rechnung bezahlt', 'span'); // this status is visible but not active
        $I->dontSee('Rechnung als bezahlt markieren'); // there is no button to mark the invoice as paid
    }
}
