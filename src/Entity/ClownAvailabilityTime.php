<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'clown_date_daytime_index', fields: ['clown', 'date', 'daytime'])]
class ClownAvailabilityTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clown $clown = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 2)]
    private ?string $daytime = null;

    #[ORM\Column(length: 20)]
    private string $availability = 'yes';

    #[ORM\ManyToOne(inversedBy: 'clownAvailabilityTimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClownAvailability $clownAvailability = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClown(): ?Clown
    {
        return $this->clown;
    }

    public function setClown(?Clown $clown): self
    {
        $this->clown = $clown;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDaytime(): ?string
    {
        return $this->daytime;
    }

    public function setDaytime(string $daytime): self
    {
        $this->daytime = $daytime;

        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->availability != 'no';
    }

    public function getClownAvailability(): ?ClownAvailability
    {
        return $this->clownAvailability;
    }

    public function setClownAvailability(?ClownAvailability $clownAvailability): self
    {
        $this->clownAvailability = $clownAvailability;

        return $this;
    }
}
