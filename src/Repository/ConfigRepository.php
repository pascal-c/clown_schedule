<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Config;
use App\Value\Preference;

class ConfigRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return Config::class;
    }

    public function isFeatureCalculationActive(): bool
    {
        return $this->find()->useCalculation();
    }

    public function isFeaturePlayDateChangeRequestsActive(): bool
    {
        return $this->find()->isFeaturePlayDateChangeRequestsActive();
    }

    public function isFeatureMaxPerWeekActive(): bool
    {
        return $this->find()->isFeatureMaxPerWeekActive();
    }

    public function isFeatureClownVenuePreferencesActive(): bool
    {
        return $this->find()->isFeatureClownVenuePreferencesActive();
    }

    public function find(): Config
    {
        return $this->doctrineRepository->find(1);
    }

    public function getFederalState(): ?string
    {
        return $this->find()->getFederalState();
    }

    public function isFeatureAssignResponsibleClownAsFirstClownActive(): bool
    {
        return $this->find()->isFeatureAssignResponsibleClownAsFirstClownActive();
    }

    public function getPointsForPreference(Preference $preference): int
    {
        $config = $this->find();

        return match ($preference) {
            Preference::BEST => $config->getPointsPerPreferenceBest(),
            Preference::BETTER => $config->getPointsPerPreferenceBetter(),
            Preference::OK => $config->getPointsPerPreferenceOk(),
            Preference::WORSE => $config->getPointsPerPreferenceWorse(),
            Preference::WORST => $config->getPointsPerPreferenceWorst(),
        };
    }
}
