<?php

namespace App\Entity;

use App\Entity\ClownAvailability;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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

    public function __construct()
    {
        $this->venues = new ArrayCollection();
        $this->venue_responsibilities = new ArrayCollection();
        $this->playDates = new ArrayCollection();
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
        foreach($this->clownAvailabilities as $availability) {
            if ($availability->getMonth() == $month) {
                return true;
            }
        }

        return false;
    }
}
