<?php

declare(strict_types=1);

namespace App\Guard;

use App\Entity\Invoice;
use App\Service\AuthService;

class InvoiceGuard
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    public function canUpdate(Invoice $invoice): bool
    {
        return $this->authService->isAdmin() || $invoice->getClown() === $this->authService->getCurrentClown();
    }
}
