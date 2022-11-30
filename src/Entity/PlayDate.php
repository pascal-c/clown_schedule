<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['venue', 'date'], message: 'Es existiert bereits ein Spieltermin für diesen Spielort am gleichen Tag.')]
class PlayDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 2)]
    private ?string $daytime = null;

    #[ORM\ManyToOne(inversedBy: 'playDates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Venue $venue = null;

    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'playDates')]
    private Collection $playingClowns;

    public function __construct()
    {
        $this->playingClowns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getMonth(): Month
    {
        return new Month(\DateTimeImmutable::createFromMutable($this->date));
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

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return Collection<int, Clown>
     */
    public function getPlayingClowns(): Collection
    {
        return $this->playingClowns;
    }

    public function addPlayingClown(Clown $playingClown): self
    {
        if (!$this->playingClowns->contains($playingClown)) {
            $this->playingClowns->add($playingClown);
        }

        return $this;
    }

    public function removePlayingClown(Clown $playingClown): self
    {
        $this->playingClowns->removeElement($playingClown);

        return $this;
    }
}
