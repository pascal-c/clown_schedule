<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownRepository;
use App\Repository\PlayDateRepository;

class InvoiceService
{
    public function __construct(
        private ClownRepository $clownRepository,
        private PlayDateRepository $playDateRepository,
    ) {
    }

    public function createInvoices(Month $month): void
    {
        $clowns = $this->clownRepository->all();
        foreach ($clowns as $clown) {
            /** @var PlayDate[] $playDates */
            /** @var Clown $clown */
            $playDates = $this->playDateRepository->confirmedAndPaidByMonthAndClown($month, $clown);
            if (count($playDates) > 0) {
                $clown->addInvoice(new Invoice($month, $clown));
            }
        }
    }
}
