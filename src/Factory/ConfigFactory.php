<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Config;

class ConfigFactory extends AbstractFactory
{
    public function update(
        ?string $feeLabel = 'Honorar',
        ?string $alternativeFeeLabel = null,
        bool $featureClownVenuePreferencesActive = false,
        bool $featureAssignResponsibleClownAsFirstClown = true,
        bool $featureTeamsActive = false,
        bool $teamCanAssignPlayingClowns = false,
        bool $featureInvoiceManagementClowns = true,
        bool $featureInvoiceManagementVenues = false,
        bool $featureInvoicingClowns = false,
        bool $featureInvoicePaymentClowns = false,
    ): Config {
        /** @var Config $config */
        $config = $this->entityManager->getRepository(Config::class)->find(1);
        $config->setFeeLabel($feeLabel);
        $config->setAlternativeFeeLabel($alternativeFeeLabel);
        $config->setFeatureClownVenuePreferencesActive($featureClownVenuePreferencesActive);
        $config->setFeatureAssignResponsibleClownAsFirstClownActive($featureAssignResponsibleClownAsFirstClown);
        $config->setFeatureTeamsActive($featureTeamsActive);
        $config->setTeamCanAssignPlayingClowns($teamCanAssignPlayingClowns);
        $config->featureInvoiceManagementClowns = $featureInvoiceManagementClowns;
        $config->featureInvoiceManagementVenues = $featureInvoiceManagementVenues;
        $config->featureInvoicingClowns = $featureInvoicingClowns;
        $config->featureInvoicePaymentClowns = $featureInvoicePaymentClowns;

        $this->entityManager->flush();

        return $config;
    }
}
