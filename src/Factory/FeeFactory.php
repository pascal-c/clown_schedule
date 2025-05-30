<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Venue;
use App\Entity\Fee;
use App\Lib\Collection;
use DateTimeImmutable;

class FeeFactory extends AbstractFactory
{
    public function create(
        ?Venue $venue = null,
        ?string $validFrom = null,
        ?float $feeStandard = null,
        float|string|null $feeAlternative = 'default',
        ?int $kilometers = null,
        float $feePerKilometer = 0.35,
        ?bool $kilometersFeeForAllClowns = null,
    ): Fee {
        list($feeStandardGenerated, $feeAlternativeGenerated, $kilometersGenerated, $kilometersFeeForAllClownsGenerated) = $this->feeOptions()->sample();
        $venueFee = (new Fee())
            ->setVenue($venue)
            ->setValidFrom($validFrom ? new DateTimeImmutable($validFrom) : null)
            ->setFeeStandard($feeStandard ?? $feeStandardGenerated)
            ->setFeeAlternative('default' === $feeAlternative ? $feeAlternativeGenerated : $feeAlternative)
            ->setKilometers($kilometers ?? $kilometersGenerated)
            ->setFeePerKilometer($feePerKilometer)
            ->setKilometersFeeForAllClowns($kilometersFeeForAllClowns ?? $kilometersFeeForAllClownsGenerated)
        ;

        $this->entityManager->persist($venueFee);
        $this->entityManager->flush();

        return $venueFee;
    }

    private function feeOptions(): Collection
    {
        return new Collection([
            [120.0, 110.0, 50, true],
            [150.0, 150.0, null, true],
            [130.0, 120.0, 100, true],
            [160.0, 140.0, 150, false],
            [100.0, 100.0, null, true],
        ]);
    }
}
