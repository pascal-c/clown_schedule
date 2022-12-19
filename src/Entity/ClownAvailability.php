<?php

namespace App\Entity;

use App\Entity\Month;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ClownAvailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clownAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clown $clown = null;

    #[ORM\Column(length: 7)]
    private ?string $month = null;

    #[ORM\Column]
    private ?int $max_plays_month = 10;

    #[ORM\Column]
    private ?int $wished_plays_month = 5;

    #[ORM\Column]
    private ?int $max_plays_day = 1;

    #[ORM\OneToMany(mappedBy: 'clownAvailability', targetEntity: ClownAvailabilityTime::class, orphanRemoval: true)]
    private Collection $clownAvailabilityTimes;

    #[ORM\Column(nullable: true)]
    private ?float $entitled_plays_month = null;

    #[ORM\Column(nullable: true)]
    private ?int $calculated_plays_month = null;

    public function __construct()
    {
        $this->clownAvailabilityTimes = new ArrayCollection();
    }

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

    public function getMonth(): ?Month
    {
        return new Month(new \DateTimeImmutable($this->month));
    }

    public function setMonth(Month $month): self
    {
        $this->month = $month->getKey();

        return $this;
    }

    public function getMaxPlaysMonth(): ?int
    {
        return $this->max_plays_month;
    }

    public function setMaxPlaysMonth(int $max_plays_month): self
    {
        $this->max_plays_month = $max_plays_month;

        return $this;
    }

    public function getWishedPlaysMonth(): ?int
    {
        return $this->wished_plays_month;
    }

    public function setWishedPlaysMonth(int $wished_plays_month): self
    {
        $this->wished_plays_month = $wished_plays_month;

        return $this;
    }

    public function getMaxPlaysDay(): ?int
    {
        return $this->max_plays_day;
    }

    public function setMaxPlaysDay(int $max_plays_day): self
    {
        $this->max_plays_day = $max_plays_day;

        return $this;
    }

    /**
     * @return Collection<int, ClownAvailabilityTime>
     */
    public function getClownAvailabilityTimes(): Collection
    {
        return $this->clownAvailabilityTimes;
    }

    public function addClownAvailabilityTime(ClownAvailabilityTime $clownAvailabilityTime): self
    {
        if (!$this->clownAvailabilityTimes->contains($clownAvailabilityTime)) {
            $this->clownAvailabilityTimes->add($clownAvailabilityTime);
            $clownAvailabilityTime->setClownAvailability($this);
        }

        return $this;
    }

    public function removeClownAvailabilityTime(ClownAvailabilityTime $clownAvailabilityTime): self
    {
        if ($this->clownAvailabilityTimes->removeElement($clownAvailabilityTime)) {
            // set the owning side to null (unless already changed)
            if ($clownAvailabilityTime->getClownAvailability() === $this) {
                $clownAvailabilityTime->setClownAvailability(null);
            }
        }

        return $this;
    }

    public function getAvailabilityRatio(): float
    {
        $allTimeSlots = $this->getClownAvailabilityTimes(); 
        $availableTimeSlots = $allTimeSlots->filter(
            fn($timeSlot) => $timeSlot->getAvailability() != 'no'
        );

        return count($availableTimeSlots) / count($allTimeSlots);   
    }

    private function getTimeSlot(\DateTimeInterface $date, string $daytime): ClownAvailabilityTime
    {
        return $this->getClownAvailabilityTimes()
            ->filter(fn(ClownAvailabilityTime $timeSlot) => $timeSlot->getDate() == $date && $timeSlot->getDaytime() == $daytime)
            ->first();
    }

    public function getAvailabilityOn(\DateTimeInterface $date, string $daytime): string
    {
        return $this->getTimeSlot($date, $daytime)
            ->getAvailability();
    }

    public function isAvailableOn(\DateTimeInterface $date, string $daytime): bool
    {
        return $this->getTimeSlot($date, $daytime)
            ->isAvailable(); 
    }

    public function getEntitledPlaysMonth(): ?float
    {
        return $this->entitled_plays_month;
    }

    public function setEntitledPlaysMonth(?float $entitled_plays_month): self
    {
        $this->entitled_plays_month = $entitled_plays_month;

        return $this;
    }

    public function getCalculatedPlaysMonth(): ?int
    {
        return $this->calculated_plays_month;
    }

    public function setCalculatedPlaysMonth(?int $calculated_plays_month): self
    {
        $this->calculated_plays_month = $calculated_plays_month;

        return $this;
    }

    public function incCalculatedPlaysMonth(): self
    {
        if (is_null($this->calculated_plays_month)) {
            $this->calculated_plays_month = 1;
        } else {
            $this->calculated_plays_month++;
        }

        return $this;
    }

    public function getOpenEntitledPlays(): ?float
    {
        if (is_null($this->getEntitledPlaysMonth())) {
            return null;
        }

        return $this->getEntitledPlaysMonth() - $this->getCalculatedPlaysMonth();
    }
}
