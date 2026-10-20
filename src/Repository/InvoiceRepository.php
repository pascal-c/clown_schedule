<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\Month;

class InvoiceRepository extends AbstractRepository
{
    public function __construct(private MonthRepository $monthRepository)
    {
    }

    protected function getEntityName(): string
    {
        return Invoice::class;
    }

    /**
     * @return Invoice[]
     */
    public function findOpenOverDue(): array
    {
        return $this->doctrineRepository->createQueryBuilder('invoice')
            ->where('invoice.status = :status')
            ->andWhere('invoice.month <= :month')
            ->setParameter('status', Invoice::STATUS_OPEN)
            ->setParameter('month', $this->getOpenOverDueMonth()->getKey())
            ->orderBy('invoice.clown', 'ASC')
            ->addOrderBy('invoice.month', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getOpenOverDueMonth(): Month
    {
        return $this->monthRepository->current()->previous()->previous();
    }

    public function findIssued(): array
    {
        return $this->doctrineRepository->createQueryBuilder('invoice')
            ->where('invoice.status = :status')
            ->setParameter('status', Invoice::STATUS_ISSUED)
            ->orderBy('invoice.clown', 'ASC')
            ->orderBy('invoice.month', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
