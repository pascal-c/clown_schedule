<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use DateTimeImmutable;
use Symfony\Contracts\Service\Attribute\Required;

class InvoiceFactory extends AbstractFactory
{
    private ClownFactory $clownFactory;

    #[Required]
    public function _inject(ClownFactory $clownFactory)
    {
        $this->clownFactory = $clownFactory;
    }

    public function create(
        ?Clown $clown = null,
        ?Month $month = null,
        string $status = Invoice::STATUS_OPEN,
    ): Invoice {
        $invoice = new Invoice($month ?? Month::build((new DateTimeImmutable())->format('Y-m')), $clown ?? $this->clownFactory->create())
            ->setStatus($status)
        ;

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }
}
