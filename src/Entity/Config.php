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

    #[ORM\Column(length: 255, nullable: true, options: ['default' => 'Honorar'])]
    private ?string $feeLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alternativeFeeLabel = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $featurePlayDateChangeRequests = true;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $featureAssignResponsibleClownAsFirstClown = null;

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

    public function isFeatureFeeActive(): bool
    {
        return null !== $this->feeLabel;
    }

    public function isFeatureAlternativeFeeActive(): bool
    {
        return null !== $this->alternativeFeeLabel;
    }

    public function getFeeLabel(): ?string
    {
        return $this->feeLabel;
    }

    public function setFeeLabel(?string $feeLabel): static
    {
        $this->feeLabel = $feeLabel;

        return $this;
    }

    public function getAlternativeFeeLabel(): ?string
    {
        return $this->alternativeFeeLabel;
    }

    public function setAlternativeFeeLabel(?string $alternativeFeeLabel): static
    {
        $this->alternativeFeeLabel = $alternativeFeeLabel;

        return $this;
    }

    public function isFeaturePlayDateChangeRequestsActive(): bool
    {
        return $this->featurePlayDateChangeRequests;
    }

    public function setFeaturePlayDateChangeRequestsActive(bool $featurePlayDateChangeRequests): static
    {
        $this->featurePlayDateChangeRequests = $featurePlayDateChangeRequests;

        return $this;
    }

    public function isFeatureAssignResponsibleClownAsFirstClownActive(): ?bool
    {
        return $this->featureAssignResponsibleClownAsFirstClown;
    }

    public function setFeatureAssignResponsibleClownAsFirstClownActive(bool $featureAssignResponsibleClownAsFirstClown): static
    {
        $this->featureAssignResponsibleClownAsFirstClown = $featureAssignResponsibleClownAsFirstClown;

        return $this;
    }
}
