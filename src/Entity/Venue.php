<?php

namespace App\Entity;

use App\Value\Preference;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity('name')]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: PlayDate::class)]
    #[ORM\OrderBy(['date' => 'ASC'])]
    private Collection $playDates;

    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'venue_responsibilities')]
    private Collection $responsibleClowns;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $daytime_default = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $meetingTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $playTimeFrom = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $playTimeTo = null;

    #[ORM\Column]
    private bool $isSuper = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetAndNumber = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comments = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $officialName = null;

    #[ORM\Column]
    private bool $archived = false;

    #[ORM\JoinTable(name: 'venue_clown_blocked')]
    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'blockedVenues', )]
    private Collection $blockedClowns;

    /**
     * @var Collection<int, Fee>
     */
    #[ORM\OneToMany(targetEntity: Fee::class, mappedBy: 'venue', orphanRemoval: true)]
    #[ORM\OrderBy(['validFrom' => 'DESC'])]
    private Collection $fees;

    /**
     * @var Collection<int, Contact>
     */
    #[ORM\ManyToMany(targetEntity: Contact::class)]
    private Collection $contacts;

    /**
     * @var Collection<int, RecurringDate>
     */
    #[ORM\OneToMany(targetEntity: RecurringDate::class, mappedBy: 'venue')]
    private Collection $recurringDates;

    /**
     * @var Collection<int, ClownVenuePreference>
     */
    #[ORM\OneToMany(targetEntity: ClownVenuePreference::class, mappedBy: 'venue', orphanRemoval: true)]
    private Collection $clownVenuePreferences;

    #[ORM\Column(options: ['default' => false])]
    private bool $assignResponsibleClownAsFirstClown = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $teamActive = false;

    /**
     * @var Collection<int, Clown>
     */
    #[ORM\JoinTable(name: 'venue_team')]
    #[ORM\ManyToMany(targetEntity: Clown::class)]
    private Collection $team;

    public function __construct()
    {
        $this->playDates = new ArrayCollection();
        $this->responsibleClowns = new ArrayCollection();
        $this->blockedClowns = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->recurringDates = new ArrayCollection();
        $this->clownVenuePreferences = new ArrayCollection();
        $this->team = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPlayDates(): Collection
    {
        return $this->playDates;
    }

    public function hasPlayDates(): bool
    {
        return !$this->playDates->isEmpty();
    }

    public function addPlayDate(PlayDate $playDate): self
    {
        if (!$this->playDates->contains($playDate)) {
            $this->playDates->add($playDate);
            $playDate->setVenue($this);
        }

        return $this;
    }

    public function removePlayDate(PlayDate $playDate): self
    {
        if ($this->playDates->removeElement($playDate)) {
            // set the owning side to null (unless already changed)
            if ($playDate->getVenue() === $this) {
                $playDate->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Clown>
     */
    public function getResponsibleClowns(): Collection
    {
        return $this->responsibleClowns;
    }

    public function addResponsibleClown(Clown $responsibleClown): self
    {
        if (!$this->responsibleClowns->contains($responsibleClown)) {
            $this->responsibleClowns->add($responsibleClown);
        }

        return $this;
    }

    public function removeResponsibleClown(Clown $responsibleClown): self
    {
        $this->responsibleClowns->removeElement($responsibleClown);

        return $this;
    }

    public function getDaytimeDefault(): ?string
    {
        return $this->daytime_default;
    }

    public function setDaytimeDefault(?string $daytime_default): self
    {
        $this->daytime_default = $daytime_default;

        return $this;
    }

    public function getMeetingTime(): ?DateTimeImmutable
    {
        return $this->meetingTime;
    }

    public function setMeetingTime(?DateTimeImmutable $meetingTime): self
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    public function getPlayTimeFrom(): ?DateTimeImmutable
    {
        return $this->playTimeFrom;
    }

    public function setPlayTimeFrom(?DateTimeImmutable $playTimeFrom): self
    {
        $this->playTimeFrom = $playTimeFrom;

        return $this;
    }

    public function getPlayTimeTo(): ?DateTimeImmutable
    {
        return $this->playTimeTo;
    }

    public function setPlayTimeTo(?DateTimeImmutable $playTimeTo): self
    {
        $this->playTimeTo = $playTimeTo;

        return $this;
    }

    public function isSuper(): bool
    {
        return $this->isSuper;
    }

    public function setIsSuper(bool $isSuper): self
    {
        $this->isSuper = $isSuper;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getStreetAndNumber(): ?string
    {
        return $this->streetAndNumber;
    }

    public function setStreetAndNumber(?string $streetAndNumber): self
    {
        $this->streetAndNumber = $streetAndNumber;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getOfficialName(): ?string
    {
        return $this->officialName ?? $this->getName();
    }

    public function setOfficialName(?string $officialName): static
    {
        $this->officialName = $officialName;

        return $this;
    }

    public function isActive(): bool
    {
        return !$this->archived;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return Collection<int, Clown>
     */
    public function getBlockedClowns(): Collection
    {
        return $this->blockedClowns;
    }

    public function addBlockedClown(Clown $blockedClown): static
    {
        if (!$this->blockedClowns->contains($blockedClown)) {
            $this->blockedClowns->add($blockedClown);
        }

        return $this;
    }

    public function removeBlockedClown(Clown $blockedClown): static
    {
        $this->blockedClowns->removeElement($blockedClown);

        return $this;
    }

    public function getFeeFor(DateTimeImmutable $date): ?Fee
    {
        foreach ($this->getFees() as $fee) { // fees are sorted by date desc...
            if ($date >= $fee->getValidFrom()) {
                return $fee;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Fee>
     */
    public function getFees(): Collection
    {
        return $this->fees;
    }

    public function addFee(Fee $fee): static
    {
        if (!$this->fees->contains($fee)) {
            $this->fees->add($fee);
            $fee->setVenue($this);
        }

        return $this;
    }

    public function removeFee(Fee $fee): static
    {
        if ($this->fees->removeElement($fee)) {
            // set the owning side to null (unless already changed)
            if ($fee->getVenue() === $this) {
                $fee->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * @return Collection<int, RecurringDate>
     */
    public function getRecurringDates(): Collection
    {
        return $this->recurringDates;
    }

    public function addRecurringDate(RecurringDate $recurringDate): static
    {
        if (!$this->recurringDates->contains($recurringDate)) {
            $this->recurringDates->add($recurringDate);
            $recurringDate->setVenue($this);
        }

        return $this;
    }

    public function removeRecurringDate(RecurringDate $recurringDate): static
    {
        if ($this->recurringDates->removeElement($recurringDate)) {
            // set the owning side to null (unless already changed)
            if ($recurringDate->getVenue() === $this) {
                $recurringDate->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClownVenuePreference>
     */
    public function getClownVenuePreferences(): Collection
    {
        return $this->clownVenuePreferences;
    }

    public function addClownVenuePreference(ClownVenuePreference $clownVenuePreference): static
    {
        if (!$this->clownVenuePreferences->contains($clownVenuePreference)) {
            $this->clownVenuePreferences->add($clownVenuePreference);
            $clownVenuePreference->setVenue($this);
        }

        return $this;
    }

    public function getAveragePreference(): Preference
    {
        if ($this->clownVenuePreferences->isEmpty()) {
            return Preference::OK;
        }

        $preferencesInPoints = $this->clownVenuePreferences->map(
            fn (ClownVenuePreference $preference): int => $preference->getPreference()->int()
        );
        $totalPoints = array_sum($preferencesInPoints->toArray());

        $averagePoints = intval(round($totalPoints / $preferencesInPoints->count()));

        return Preference::fromInt($averagePoints);
    }

    public function assignResponsibleClownAsFirstClown(): ?bool
    {
        return $this->assignResponsibleClownAsFirstClown;
    }

    public function setAssignResponsibleClownAsFirstClown(bool $assignResponsibleClownAsFirstClown): static
    {
        $this->assignResponsibleClownAsFirstClown = $assignResponsibleClownAsFirstClown;

        return $this;
    }

    public function isClownsteamActive(): ?bool
    {
        return $this->clownsteamActive;
    }

    public function setClownsteamActive(bool $clownsteamActive): static
    {
        $this->clownsteamActive = $clownsteamActive;

        return $this;
    }

    public function isTeamActive(): ?bool
    {
        return $this->teamActive;
    }

    public function setTeamActive(bool $teamActive): static
    {
        $this->teamActive = $teamActive;

        return $this;
    }

    /**
     * @return Collection<int, Clown>
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    public function addTeam(Clown $team): static
    {
        if (!$this->team->contains($team)) {
            $this->team->add($team);
        }

        return $this;
    }

    public function removeTeam(Clown $team): static
    {
        $this->team->removeElement($team);

        return $this;
    }
}
