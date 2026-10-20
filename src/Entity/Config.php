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
    public ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $specialPlayDateUrl = null;

    #[ORM\Column]
    public bool $featureMaxPerWeekActive = false;

    #[ORM\Column(length: 20, nullable: true)]
    public ?string $federalState = null;

    #[ORM\Column(options: ['default' => true])]
    public bool $useCalculation = true;

    #[ORM\Column(length: 255, nullable: true, options: ['default' => 'Honorar'])]
    public ?string $feeLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $alternativeFeeLabel = null;

    #[ORM\Column(options: ['default' => true])]
    public ?bool $featurePlayDateChangeRequests = true;

    #[ORM\Column(options: ['default' => true])]
    public ?bool $featureAssignResponsibleClownAsFirstClown = null;

    #[ORM\Column(options: ['default' => 1000])]
    #[Assert\Range(min: 0, max: 1000)]
    public ?int $pointsPerMissingPerson = null;

    #[ORM\Column(options: ['default' => 10])]
    #[Assert\Range(min: 0, max: 1000)]
    public ?int $pointsPerMaybePerson = null;

    #[ORM\Column(options: ['default' => 20])]
    #[Assert\Range(min: 0, max: 1000)]
    public ?int $pointsPerTargetShifts = null;

    #[ORM\Column(options: ['default' => 100])]
    #[Assert\Range(min: 0, max: 1000)]
    public ?int $pointsPerMaxPerWeek = null;

    #[ORM\Column(options: ['default' => 10])]
    public int $pointsPerPreferenceWorst;

    #[ORM\Column(options: ['default' => 4])]
    public int $pointsPerPreferenceWorse;

    #[ORM\Column(options: ['default' => 2])]
    public int $pointsPerPreferenceOk;

    #[ORM\Column(options: ['default' => 1])]
    public int $pointsPerPreferenceBetter;

    #[ORM\Column(options: ['default' => 0])]
    public int $pointsPerPreferenceBest;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureClownVenuePreferences = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureTeams = false;

    #[ORM\Column(options: ['default' => 30])]
    public int $pointsPerPersonNotInTeam = 30;

    #[ORM\Column(options: ['default' => false])]
    public ?bool $teamCanAssignPlayingClowns = null;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureAvoidOnlyMen = false;

    #[ORM\Column(options: ['default' => true])]
    public bool $featureInvoiceManagementClowns = true;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureInvoiceManagementVenues = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureInvoicingClowns = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $featureInvoicePaymentClowns = false;

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

    public function hasFeatureAlternativeFee(): bool
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

    public function isFeatureClownVenuePreferencesActive(): bool
    {
        return $this->featureClownVenuePreferences;
    }

    public function setFeatureClownVenuePreferencesActive(bool $featureClownVenuePreferences): static
    {
        $this->featureClownVenuePreferences = $featureClownVenuePreferences;

        return $this;
    }

    public function isFeatureTeamsActive(): bool
    {
        return $this->featureTeams;
    }

    public function setFeatureTeamsActive(bool $featureTeams): static
    {
        $this->featureTeams = $featureTeams;

        return $this;
    }

    public function getPointsPerPersonNotInTeam(): int
    {
        return $this->pointsPerPersonNotInTeam;
    }

    public function setPointsPerPersonNotInTeam(int $pointsPerPersonNotInTeam): static
    {
        $this->pointsPerPersonNotInTeam = $pointsPerPersonNotInTeam;

        return $this;
    }

    public function teamCanAssignPlayingClowns(): ?bool
    {
        return $this->teamCanAssignPlayingClowns;
    }

    public function setTeamCanAssignPlayingClowns(bool $teamCanAssignPlayingClowns): static
    {
        $this->teamCanAssignPlayingClowns = $teamCanAssignPlayingClowns;

        return $this;
    }

    public function isfeatureAvoidOnlyMenActive(): bool
    {
        return $this->featureAvoidOnlyMen;
    }

    public function setfeatureAvoidOnlyMenActive(bool $featureAvoidOnlyMen): static
    {
        $this->featureAvoidOnlyMen = $featureAvoidOnlyMen;

        return $this;
    }
}
