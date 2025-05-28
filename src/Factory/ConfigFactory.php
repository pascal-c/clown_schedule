<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Config;

class ConfigFactory extends AbstractFactory
{
    public function update(
        ?string $feeLabel = 'Honorar',
        ?string $alternativeFeeLabel = null,
    ): Config {
        $config = $this->entityManager->getRepository(Config::class)->find(1);
        $config->setFeeLabel($feeLabel);
        $config->setAlternativeFeeLabel($alternativeFeeLabel);
        $this->entityManager->flush();

        return $config;
    }
}
