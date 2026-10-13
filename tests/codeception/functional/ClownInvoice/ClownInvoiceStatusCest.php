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

    public function InvoiceWorkflowForAdmin(AdminTester $I): void
    {
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
}
