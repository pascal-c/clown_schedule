<?php

namespace App\Entity;

use App\Value\TimeSlot;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['venue', 'date'], message: 'Es existiert bereits ein Spieltermin für diesen Spielort am gleichen Tag.')]
class PlayDate implements TimeSlotPeriodInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 3)]
    private ?string $daytime = null;

    #[ORM\ManyToOne(inversedBy: 'playDates')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\When(
        expression: '!this.isSpecial()',
        constraints: [
            new Assert\NotBlank()
        ],
    )]
    private ?Venue $venue = null;

    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'playDates')]
    private Collection $playingClowns;

    #[ORM\Column]
    private ?bool $isSpecial = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

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
        return new Month($this->date);
    }   

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
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

    public function getTimeSlots(): array
    {
        if (TimeSlotPeriodInterface::ALL === $this->getDaytime()) {
            return [
                new TimeSlot($this->getDate(), TimeSlotInterface::AM),
                new TimeSlot($this->getDate(), TimeSlotInterface::PM),
            ];
        }

        return [new TimeSlot($this->getDate(), $this->getDaytime())];
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

    public function isSpecial(): bool
    {
        return $this->isSpecial;
    }

    public function setIsSpecial(bool $isSpecial): self
    {
        $this->isSpecial = $isSpecial;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
