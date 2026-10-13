<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class ClownTest extends TestCase
{
    public function testAddInvoice(): void
    {
        $clown = new Clown();
        $month = new Month(new DateTimeImmutable('2023-01'));
        $invoice1 = new Invoice($month, $clown);
        $invoice2 = new Invoice($month, $clown);

        $clown->addInvoice($invoice1);
        $clown->addInvoice($invoice2); // Should not add a duplicate invoice for the same month

        $this->assertCount(1, $clown->getInvoices());
        $this->assertSame($invoice1, $clown->getInvoices()[0]);
    }
}
