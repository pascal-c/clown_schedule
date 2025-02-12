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
        return $this->doctrineRepository->find(1)->isFeatureMaxPerWeekActive();
    }

    public function find(): Config
    {
        return $this->doctrineRepository->find(1);
    }
}
