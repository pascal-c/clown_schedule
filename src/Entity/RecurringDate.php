<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
class RecurringDate
{
    public const RHYTHM_WEEKLY = 'weekly';
    public const RHYTHM_MONTHLY = 'monthly';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recurringDates')]
    private ?Venue $venue = null;

    #[ORM\Column(length: 3)]
    private ?string $daytime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?DateTimeImmutable $meetingTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?DateTimeImmutable $playTimeFrom = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?DateTimeImmutable $playTimeTo = null;

    #[ORM\Column]
    private ?bool $isSuper = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 10)]
    private ?string $rhythm = null;

    #[ORM\Column(length: 10)]
    private ?string $dayOfWeek = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $every = 1;

    /**
     * @var Collection<int, PlayDate>
     */
    #[ORM\OneToMany(targetEntity: PlayDate::class, mappedBy: 'recurringDate', cascade: ['persist'])]
    private Collection $playDates;

    public function __construct()
    {
        $this->playDates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDaytime(): ?string
    {
        return $this->daytime;
    }

    public function setDaytime(string $daytime): static
    {
        $this->daytime = $daytime;

        return $this;
    }

    public function getMeetingTime(): ?DateTimeImmutable
    {
        return $this->meetingTime;
    }

    public function setMeetingTime(DateTimeImmutable $meetingTime): static
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    public function getPlayTimeFrom(): ?DateTimeImmutable
    {
        return $this->playTimeFrom;
    }

    public function setPlayTimeFrom(DateTimeImmutable $playTimeFrom): static
    {
        $this->playTimeFrom = $playTimeFrom;

        return $this;
    }

    public function getPlayTimeTo(): ?DateTimeImmutable
    {
        return $this->playTimeTo;
    }

    public function setPlayTimeTo(DateTimeImmutable $playTimeTo): static
    {
        $this->playTimeTo = $playTimeTo;

        return $this;
    }

    public function isSuper(): ?bool
    {
        return $this->isSuper;
    }

    public function setIsSuper(bool $isSuper): static
    {
        $this->isSuper = $isSuper;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isWeekly(): bool
    {
        return self::RHYTHM_WEEKLY === $this->rhythm;
    }

    public function getRhythm(): ?string
    {
        return $this->rhythm;
    }

    public function setRhythm(string $rhythm): static
    {
        $this->rhythm = $rhythm;

        return $this;
    }

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getEvery(): int
    {
        return $this->every;
    }

    public function setEvery(int $every): static
    {
        $this->every = $every;

        return $this;
    }

    /**
     * @return Collection<int, PlayDate>
     */
    public function getPlayDates(): Collection
    {
        return $this->playDates;
    }

    public function addPlayDate(PlayDate $playDate): static
    {
        if (!$this->playDates->contains($playDate)) {
            $this->playDates->add($playDate);
            $playDate->setRecurringDate($this);
        }

        return $this;
    }

    public function removePlayDate(PlayDate $playDate): static
    {
        if ($this->playDates->removeElement($playDate)) {
            // set the owning side to null (unless already changed)
            if ($playDate->getRecurringDate() === $this) {
                $playDate->setRecurringDate(null);
            }
        }

        return $this;
    }
}
