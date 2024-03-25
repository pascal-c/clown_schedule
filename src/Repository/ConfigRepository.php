<?php

declare(strict_types=1);

namespace App\Repository;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ConfigRepository
{
    public function __construct(private ContainerBagInterface $containerBag)
    {
    }

    public function hasFeatureMaxPerWeek(): bool
    {
        return $this->containerBag->get('app.feature_max_per_week');
    }
}
