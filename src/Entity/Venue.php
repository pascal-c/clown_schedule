<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeInterface;

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

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $meetingTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $playTimeFrom = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $playTimeTo = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPerson = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $feeByCar = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $feeByPublicTransport = null;

    #[ORM\Column(nullable: true)]
    private ?int $kilometers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comments = null;

    #[ORM\Column]
    private bool $kilometersFeeForAllClowns = true;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private float $feePerKilometer = 0.35;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $officialName = null;

    #[ORM\Column]
    private bool $archived = false;

    #[ORM\JoinTable(name: 'venue_clown_blocked')]
    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'blockedVenues', )]
    private Collection $blockedClowns;

    public function __construct()
    {
        $this->playDates = new ArrayCollection();
        $this->responsibleClowns = new ArrayCollection();
        $this->blockedClowns = new ArrayCollection();
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

    public function getMeetingTime(): ?DateTimeInterface
    {
        return $this->meetingTime;
    }

    public function setMeetingTime(?DateTimeInterface $meetingTime): self
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    public function getPlayTimeFrom(): ?DateTimeInterface
    {
        return $this->playTimeFrom;
    }

    public function setPlayTimeFrom(?DateTimeInterface $playTimeFrom): self
    {
        $this->playTimeFrom = $playTimeFrom;

        return $this;
    }

    public function getPlayTimeTo(): ?DateTimeInterface
    {
        return $this->playTimeTo;
    }

    public function setPlayTimeTo(?DateTimeInterface $playTimeTo): self
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

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): self
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): self
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getFeeByCar(): ?float
    {
        return $this->feeByCar;
    }

    public function setFeeByCar(?float $feeByCar): self
    {
        $this->feeByCar = $feeByCar;

        return $this;
    }

    public function getFeeByPublicTransport(): ?float
    {
        return $this->feeByPublicTransport;
    }

    public function setFeeByPublicTransport(?float $feeByPublicTransport): self
    {
        $this->feeByPublicTransport = $feeByPublicTransport;

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

    public function setKilometers(?int $kilometers): self
    {
        $this->kilometers = $kilometers;

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

    public function isKilometersFeeForAllClowns(): bool
    {
        return $this->kilometersFeeForAllClowns;
    }

    public function setKilometersFeeForAllClowns(bool $kilometersFeeForAllClowns): self
    {
        $this->kilometersFeeForAllClowns = $kilometersFeeForAllClowns;

        return $this;
    }

    public function getFeePerKilometer(): float
    {
        return $this->feePerKilometer;
    }

    public function setFeePerKilometer(float $feePerKilometer): self
    {
        $this->feePerKilometer = $feePerKilometer;

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
}
