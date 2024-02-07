<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity('name')]
class Clown
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Your name must be at least {{ limit }} characters long',
        maxMessage: 'Your name cannot be longer than {{ limit }} characters',
    )]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Venue::class, mappedBy: 'responsibleClowns')]
    private Collection $venue_responsibilities;

    #[ORM\ManyToMany(targetEntity: PlayDate::class, mappedBy: 'playingClowns')]
    private Collection $playDates;

    #[ORM\OneToMany(mappedBy: 'clown', targetEntity: ClownAvailability::class, orphanRemoval: true)]
    private Collection $clownAvailabilities;

    #[ORM\Column(length: 7)]
    private ?string $gender = null;

    #[ORM\OneToMany(mappedBy: 'substitutionClown', targetEntity: Substitution::class)]
    private Collection $substitutionTimeSlots;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'Das ist keine gültige Email')]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isAdmin = false;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\ManyToMany(targetEntity: Venue::class, mappedBy: 'blockedClowns')]
    private Collection $blockedVenues;

    public function __construct()
    {
        $this->venue_responsibilities = new ArrayCollection();
        $this->playDates = new ArrayCollection();
        $this->substitutionTimeSlots = new ArrayCollection();
        $this->blockedVenues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    /**
     * @return Collection<int, Venue>
     */
    public function getVenueResponsibilities(): Collection
    {
        return $this->venue_responsibilities;
    }

    public function addVenueResponsibility(Venue $venueResponsibility): self
    {
        if (!$this->venue_responsibilities->contains($venueResponsibility)) {
            $this->venue_responsibilities->add($venueResponsibility);
            $venueResponsibility->addResponsibleClown($this);
        }

        return $this;
    }

    public function removeVenueResponsibility(Venue $venueResponsibility): self
    {
        if ($this->venue_responsibilities->removeElement($venueResponsibility)) {
            $venueResponsibility->removeResponsibleClown($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PlayDate>
     */
    public function getPlayDates(): Collection
    {
        return $this->playDates;
    }

    public function addPlayDate(PlayDate $playDate): self
    {
        if (!$this->playDates->contains($playDate)) {
            $this->playDates->add($playDate);
            $playDate->addPlayingClown($this);
        }

        return $this;
    }

    public function removePlayDate(PlayDate $playDate): self
    {
        if ($this->playDates->removeElement($playDate)) {
            $playDate->removePlayingClown($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ClownAvailabilities>
     */
    public function getClownAvailabilities(): Collection
    {
        return $this->clownAvailabilities;
    }

    public function hasAvailabilityFor(Month $month): bool
    {
        foreach ($this->clownAvailabilities as $availability) {
            if ($availability->getMonth() == $month) {
                return true;
            }
        }

        return false;
    }

    public function getAvailabilityFor(Month $month): ?ClownAvailability
    {
        foreach ($this->clownAvailabilities as $availability) {
            if ($availability->getMonth() == $month) {
                return $availability;
            }
        }

        return null;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection<int, Substitution>
     */
    public function getSubstitutionTimeSlots(): Collection
    {
        return $this->substitutionTimeSlots;
    }

    public function addSubstitutionTimeSlot(Substitution $substitutionTimeSlot): self
    {
        if (!$this->substitutionTimeSlots->contains($substitutionTimeSlot)) {
            $this->substitutionTimeSlots->add($substitutionTimeSlot);
            $substitutionTimeSlot->setSubstitutionClown($this);
        }

        return $this;
    }

    public function removeSubstitutionTimeSlot(Substitution $substitutionTimeSlot): self
    {
        if ($this->substitutionTimeSlots->removeElement($substitutionTimeSlot)) {
            // set the owning side to null (unless already changed)
            if ($substitutionTimeSlot->getSubstitutionClown() === $this) {
                $substitutionTimeSlot->setSubstitutionClown(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Venue>
     */
    public function getBlockedVenues(): Collection
    {
        return $this->blockedVenues;
    }

    public function addBlockedVenue(Venue $blockedVenue): static
    {
        if (!$this->blockedVenues->contains($blockedVenue)) {
            $this->blockedVenues->add($blockedVenue);
            $blockedVenue->addBlockedClown($this);
        }

        return $this;
    }

    public function removeBlockedVenue(Venue $blockedVenue): static
    {
        if ($this->blockedVenues->removeElement($blockedVenue)) {
            $blockedVenue->removeBlockedClown($this);
        }

        return $this;
    }
}
