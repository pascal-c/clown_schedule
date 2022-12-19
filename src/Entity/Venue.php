<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
    #[ORM\OrderBy(["date" => "ASC"])]
    private Collection $playDates;

    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'venue_responsibilities')]
    private Collection $responsibleClowns;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $daytime_default = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $meetingTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $playTimeFrom = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $playTimeTo = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $emails = ['', '', ''];

    public function __construct()
    {
        $this->playDates = new ArrayCollection();
        $this->responsibleClowns = new ArrayCollection();
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

    public function getMeetingTime(): ?\DateTimeInterface
    {
        return $this->meetingTime;
    }

    public function setMeetingTime(?\DateTimeInterface $meetingTime): self
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    public function getPlayTimeFrom(): ?\DateTimeInterface
    {
        return $this->playTimeFrom;
    }

    public function setPlayTimeFrom(?\DateTimeInterface $playTimeFrom): self
    {
        $this->playTimeFrom = $playTimeFrom;

        return $this;
    }

    public function getPlayTimeTo(): ?\DateTimeInterface
    {
        return $this->playTimeTo;
    }

    public function setPlayTimeTo(?\DateTimeInterface $playTimeTo): self
    {
        $this->playTimeTo = $playTimeTo;

        return $this;
    }

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }
}
