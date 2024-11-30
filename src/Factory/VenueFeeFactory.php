<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Venue;
use App\Entity\VenueFee;
use DateTimeImmutable;

class VenueFeeFactory extends AbstractFactory
{
    public function create(
        Venue $venue,
        ?string $validFrom = null,
        ?float $feeByPublicTransport = null,
        ?float $feeByCar = null,
        ?int $kilometers = null,
        float $feePerKilometer = 0.35,
        bool $kilometersFeeForAllClowns = true,
    ): VenueFee {
        $venueFee = (new VenueFee())
            ->setVenue($venue)
            ->setValidFrom($validFrom ? new DateTimeImmutable($validFrom) : null)
            ->setFeeByPublicTransport($feeByPublicTransport)
            ->setFeeByCar($feeByCar)
            ->setKilometers($kilometers)
            ->setFeePerKilometer($feePerKilometer)
            ->setKilometersFeeForAllClowns($kilometersFeeForAllClowns)
        ;

        $this->entityManager->persist($venueFee);
        $this->entityManager->flush();

        return $venueFee;
    }
}
