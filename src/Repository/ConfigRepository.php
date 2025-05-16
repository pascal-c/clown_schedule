<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Config;

class ConfigRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return Config::class;
    }

    public function isFeatureMaxPerWeekActive(): bool
    {
        return $this->find()->isFeatureMaxPerWeekActive();
    }

    public function find(): Config
    {
        return $this->doctrineRepository->find(1);
    }

    public function getFederalState(): ?string
    {
        return $this->find()->getFederalState();
    }

    public function useCalculation(): bool
    {
        return $this->find()->useCalculation();
    }
}
