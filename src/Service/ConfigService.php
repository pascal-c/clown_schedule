<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Config;
use App\Repository\ConfigRepository;

class ConfigService
{
    private Config $config {
        get => $this->configRepository->find();
    }

    public function __construct(private ConfigRepository $configRepository)
    {
    }

    public function hasFeatureInvoiceManagementClowns(): bool
    {
        return $this->config->featureInvoiceManagementClowns;
    }

    public function hasFeatureInvoicingClowns(): bool
    {
        return $this->config->featureInvoiceManagementClowns && $this->config->featureInvoicingClowns;
    }

    public function hasFeatureInvoicePaymentClowns(): bool
    {
        return $this->config->featureInvoiceManagementClowns && $this->config->featureInvoicePaymentClowns;
    }
}
