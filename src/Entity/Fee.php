<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

#[ORM\Entity]
#[ORM\Index(name: 'valid_from_idx', columns: ['valid_from'])]
class Fee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fees')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Venue $venue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $feeAlternative = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $feeStandard = null;

    #[ORM\Column(nullable: true)]
    private ?int $kilometers = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private float $feePerKilometer = 0.35;

    #[ORM\Column]
    private bool $kilometersFeeForAllClowns = true;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $validFrom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getFeeAlternative(): ?float
    {
        return $this->feeAlternative ?? $this->feeStandard;
    }

    public function setFeeAlternative(?float $feeAlternative): static
    {
        $this->feeAlternative = $feeAlternative;

        return $this;
    }

    public function getFeeStandard(): ?float
    {
        return $this->feeStandard;
    }

    public function setFeeStandard(?float $feeStandard): static
    {
        $this->feeStandard = $feeStandard;

        return $this;
    }

    public function getKilometersFee(): ?float
    {
        return $this->getKilometers() ? $this->getKilometers() * $this->getFeePerKilometer() : null;
    }

    public function getKilometers(): ?int
    {
        return $this->kilometers;
    }

    public function setKilometers(?int $kilometers): static
    {
        $this->kilometers = $kilometers;

        return $this;
    }

    public function getFeePerKilometer(): float
    {
        return $this->feePerKilometer;
    }

    public function setFeePerKilometer(float $feePerKilometer): static
    {
        $this->feePerKilometer = $feePerKilometer;

        return $this;
    }

    public function isKilometersFeeForAllClowns(): bool
    {
        return $this->kilometersFeeForAllClowns;
    }

    public function setKilometersFeeForAllClowns(bool $kilometersFeeForAllClowns): static
    {
        $this->kilometersFeeForAllClowns = $kilometersFeeForAllClowns;

        return $this;
    }

    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(?DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function copyFrom(Fee $fee): static
    {
        $this
            ->setFeeAlternative($fee->getFeeAlternative())
            ->setFeeStandard($fee->getFeeStandard())
            ->setKilometers($fee->getKilometers())
            ->setFeePerKilometer($fee->getFeePerKilometer())
            ->setKilometersFeeForAllClowns($fee->isKilometersFeeForAllClowns());

        return $this;
    }
}
