<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownRepository;
use App\Repository\PlayDateRepository;
use App\Service\InvoiceService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InvoiceServiceTest extends TestCase
{
    private ClownRepository&MockObject $clownRepository;
    private PlayDateRepository&MockObject $playDateRepository;
    private InvoiceService $invoiceService;

    public function setUp(): void
    {
        $this->clownRepository = $this->createMock(ClownRepository::class);
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->invoiceService = new InvoiceService($this->clownRepository, $this->playDateRepository);
    }

    public function testCreateInvoices(): void
    {
        $month = new Month(new DateTimeImmutable('2023-01'));
        $clown1 = new Clown();
        $clown2 = new Clown()->addInvoice(new Invoice($month, new Clown()));
        $this->clownRepository->expects($this->once())
            ->method('all')
            ->willReturn([$clown1, $clown2]);
        $this->playDateRepository->expects($this->exactly(2))
            ->method('confirmedAndPaidByMonthAndClown')
            ->with($month, $this->logicalOr($this->equalTo($clown1), $this->equalTo($clown2)))
            ->willReturnOnConsecutiveCalls(
                [],
                [new PlayDate()],
            );

        $this->invoiceService->createInvoices($month);

        $this->assertCount(0, $clown1->getInvoices());
        $this->assertCount(1, $clown2->getInvoices());
        $this->assertEquals($month, $clown2->getInvoices()[0]->getMonth());
    }
}
