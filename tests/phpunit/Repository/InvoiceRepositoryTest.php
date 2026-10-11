<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Invoice;
use App\Entity\Month;
use App\Factory\InvoiceFactory;
use App\Repository\InvoiceRepository;
use App\Repository\MonthRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class InvoiceRepositoryTest extends KernelTestCase
{
    private InvoiceRepository $repository;
    private MonthRepository&MockObject $monthRepository;
    private InvoiceFactory $invoiceFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->monthRepository = $this->createMock(MonthRepository::class);
        $container->set(MonthRepository::class, $this->monthRepository);
        $this->repository = $container->get(InvoiceRepository::class);

        $this->invoiceFactory = $container->get(InvoiceFactory::class);
    }

    public function testGetOpenOverDueMonth()
    {
        $this->monthRepository->expects($this->once())
            ->method('current')
            ->willReturn(Month::build('2024-06'));
        $result = $this->repository->getOpenOverDueMonth();
        $this->assertSame('2024-04', $result->getKey());
    }

    public function testFindOpenOverDue()
    {
        $currentMonth = Month::build('2024-06');
        $this->invoiceFactory->create(month: $currentMonth->previous()); // wrong month
        $invoice = $this->invoiceFactory->create(month: $currentMonth->previous()->previous()); // correct
        $this->invoiceFactory->create(month: $currentMonth->previous()->previous(), status: Invoice::STATUS_ISSUED); // wrong status
        $this->monthRepository->expects($this->once())
            ->method('current')
            ->willReturn($currentMonth);

        $result = $this->repository->findOpenOverDue();
        $this->assertCount(1, $result);
        $this->assertSame($invoice, $result[0]);
    }
}
