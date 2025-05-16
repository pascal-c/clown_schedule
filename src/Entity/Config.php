<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialPlayDateUrl = null;

    #[ORM\Column]
    private bool $featureMaxPerWeekActive = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $federalState = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $useCalculation = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecialPlayDateUrl(): string
    {
        return $this->specialPlayDateUrl ?? '';
    }

    public function setSpecialPlayDateUrl(?string $specialPlayDateUrl): static
    {
        $this->specialPlayDateUrl = $specialPlayDateUrl;

        return $this;
    }

    public function isFeatureMaxPerWeekActive(): bool
    {
        return $this->featureMaxPerWeekActive;
    }

    public function setFeatureMaxPerWeekActive(bool $featureMaxPerWeekActive): static
    {
        $this->featureMaxPerWeekActive = $featureMaxPerWeekActive;

        return $this;
    }

    public function getFederalState(): ?string
    {
        return $this->federalState;
    }

    public function setFederalState(?string $federalState): static
    {
        $this->federalState = $federalState;

        return $this;
    }

    public function useCalculation(): bool
    {
        return $this->useCalculation;
    }

    public function setUseCalculation(bool $useCalculation): static
    {
        $this->useCalculation = $useCalculation;

        return $this;
    }
}
