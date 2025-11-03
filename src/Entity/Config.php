<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[ORM\Column(options: ['default' => 1000])]
    #[Assert\Range(min: 0, max: 1000)]
    private ?int $pointsPerMissingPerson = null;

    #[ORM\Column(options: ['default' => 10])]
    #[Assert\Range(min: 0, max: 1000)]
    private ?int $pointsPerMaybePerson = null;

    #[ORM\Column(options: ['default' => 20])]
    #[Assert\Range(min: 0, max: 1000)]
    private ?int $pointsPerTargetShifts = null;

    #[ORM\Column(options: ['default' => 100])]
    #[Assert\Range(min: 0, max: 1000)]
    private ?int $pointsPerMaxPerWeek = null;

    #[ORM\Column(options: ['default' => 10])]
    private int $pointsPerPreferenceWorst;

    #[ORM\Column(options: ['default' => 4])]
    private int $pointsPerPreferenceWorse;

    #[ORM\Column(options: ['default' => 2])]
    private int $pointsPerPreferenceOk;

    #[ORM\Column(options: ['default' => 1])]
    private int $pointsPerPreferenceBetter;

    #[ORM\Column(options: ['default' => 0])]
    private int $pointsPerPreferenceBest;

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

    public function getPointsPerMissingPerson(): ?int
    {
        return $this->pointsPerMissingPerson;
    }

    public function setPointsPerMissingPerson(int $pointsPerMissingPerson): static
    {
        $this->pointsPerMissingPerson = $pointsPerMissingPerson;

        return $this;
    }

    public function getPointsPerMaybePerson(): ?int
    {
        return $this->pointsPerMaybePerson;
    }

    public function setPointsPerMaybePerson(int $pointsPerMaybePerson): static
    {
        $this->pointsPerMaybePerson = $pointsPerMaybePerson;

        return $this;
    }

    public function getPointsPerTargetShifts(): ?int
    {
        return $this->pointsPerTargetShifts;
    }

    public function setPointsPerTargetShifts(int $pointsPerTargetShifts): static
    {
        $this->pointsPerTargetShifts = $pointsPerTargetShifts;

        return $this;
    }

    public function getPointsPerMaxPerWeek(): ?int
    {
        return $this->pointsPerMaxPerWeek;
    }

    public function setPointsPerMaxPerWeek(int $pointsPerMaxPerWeek): static
    {
        $this->pointsPerMaxPerWeek = $pointsPerMaxPerWeek;

        return $this;
    }

    public function getPointsPerPreferenceWorst(): ?int
    {
        return $this->pointsPerPreferenceWorst;
    }

    public function setPointsPerPreferenceWorst(int $pointsPerPreferenceWorst): static
    {
        $this->pointsPerPreferenceWorst = $pointsPerPreferenceWorst;

        return $this;
    }

    public function getPointsPerPreferenceWorse(): ?int
    {
        return $this->pointsPerPreferenceWorse;
    }

    public function setPointsPerPreferenceWorse(int $pointsPerPreferenceWorse): static
    {
        $this->pointsPerPreferenceWorse = $pointsPerPreferenceWorse;

        return $this;
    }

    public function getPointsPerPreferenceOk(): ?int
    {
        return $this->pointsPerPreferenceOk;
    }

    public function setPointsPerPreferenceOk(int $pointsPerPreferenceOk): static
    {
        $this->pointsPerPreferenceOk = $pointsPerPreferenceOk;

        return $this;
    }

    public function getPointsPerPreferenceBetter(): ?int
    {
        return $this->pointsPerPreferenceBetter;
    }

    public function setPointsPerPreferenceBetter(int $pointsPerPreferenceBetter): static
    {
        $this->pointsPerPreferenceBetter = $pointsPerPreferenceBetter;

        return $this;
    }

    public function getPointsPerPreferenceBest(): ?int
    {
        return $this->pointsPerPreferenceBest;
    }

    public function setPointsPerPreferenceBest(int $pointsPerPreferenceBest): static
    {
        $this->pointsPerPreferenceBest = $pointsPerPreferenceBest;

        return $this;
    }
}
