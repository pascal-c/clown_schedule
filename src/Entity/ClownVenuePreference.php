<?php

namespace App\Entity;

use App\Value\Preference;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'clown_venue_index', fields: ['clown', 'venue'])]
class ClownVenuePreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clownVenuePreferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clown $clown = null;

    #[ORM\ManyToOne(inversedBy: 'clownVenuePreferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Venue $venue = null;

    #[ORM\Column(length: 100)]
    private ?string $preference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClown(): ?Clown
    {
        return $this->clown;
    }

    public function setClown(?Clown $clown): static
    {
        $this->clown = $clown;

        return $this;
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

    public function getPreference(): Preference
    {
        return Preference::from($this->preference);
    }

    public function setPreference(Preference $preference): static
    {
        $this->preference = $preference->value;

        return $this;
    }
}
