<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\TimeSlot;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

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
    private int $maxPlaysMonth = 10;

    #[ORM\Column]
    private int $wishedPlaysMonth = 5;

    #[ORM\Column]
    private int $maxPlaysDay = 1;

    #[ORM\OneToMany(mappedBy: 'clownAvailability', targetEntity: ClownAvailabilityTime::class, orphanRemoval: true)]
    private Collection $clownAvailabilityTimes;

    #[ORM\Column(nullable: true)]
    private ?float $entitledPlaysMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $calculatedPlaysMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetPlays = null;

    #[ORM\Column(nullable: true)]
    private ?int $calculatedSubstitutions = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $additionalWishes = null;

    #[ORM\Column(nullable: true)]
    private ?int $scheduledPlaysMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $scheduledSubstitutions = null;

    #[ORM\Column(nullable: true)]
    private ?int $softMaxPlaysWeek = null;

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
        return Month::build($this->month);
    }

    public function setMonth(Month $month): self
    {
        $this->month = $month->getKey();

        return $this;
    }

    public function getMaxPlaysMonth(): ?int
    {
        return $this->maxPlaysMonth;
    }

    public function setMaxPlaysMonth(int $maxPlaysMonth): self
    {
        $this->maxPlaysMonth = $maxPlaysMonth;

        return $this;
    }

    public function getWishedPlaysMonth(): ?int
    {
        return $this->wishedPlaysMonth;
    }

    public function setWishedPlaysMonth(int $wishedPlaysMonth): self
    {
        $this->wishedPlaysMonth = $wishedPlaysMonth;

        return $this;
    }

    public function getMaxPlaysDay(): ?int
    {
        return $this->maxPlaysDay;
    }

    public function setMaxPlaysDay(int $maxPlaysDay): self
    {
        $this->maxPlaysDay = $maxPlaysDay;

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
            fn ($timeSlot) => 'no' != $timeSlot->getAvailability()
        );

        return count($availableTimeSlots) / count($allTimeSlots);
    }

    private function getAvailabilityTimeSlot(TimeSlotInterface $timeSlot): ClownAvailabilityTime
    {
        return $this->getClownAvailabilityTimes()
            ->filter(fn (ClownAvailabilityTime $availabilityTimeSlot) => $timeSlot->getDate() == $availabilityTimeSlot->getDate() && $timeSlot->getDaytime() == $availabilityTimeSlot->getDaytime())
            ->first();
    }

    public function getAvailabilityOn(TimeSlotPeriodInterface $timeSlotPeriod): string
    {
        $result = 'yes';
        foreach ($timeSlotPeriod->getTimeSlots() as $timeSlot) {
            if ('no' === $this->getAvailabilityTimeSlot($timeSlot)->getAvailability()) {
                return 'no';
            } elseif ('maybe' === $this->getAvailabilityTimeSlot($timeSlot)->getAvailability()) {
                $result = 'maybe';
            }
        }

        return $result;
    }

    public function isAvailableOn(TimeSlotPeriodInterface $timeSlotPeriod): bool
    {
        return array_reduce(
            $timeSlotPeriod->getTimeSlots(),
            fn (bool $result, TimeSlot $timeSlot) => $result && $this->getAvailabilityTimeSlot($timeSlot)->isAvailable(),
            true,
        );
    }

    public function getEntitledPlaysMonth(): ?float
    {
        return $this->entitledPlaysMonth;
    }

    public function setEntitledPlaysMonth(?float $entitledPlaysMonth): self
    {
        $this->entitledPlaysMonth = $entitledPlaysMonth;

        return $this;
    }

    public function getCalculatedPlaysMonth(): ?int
    {
        return $this->calculatedPlaysMonth;
    }

    public function setCalculatedPlaysMonth(?int $calculatedPlaysMonth): self
    {
        $this->calculatedPlaysMonth = $calculatedPlaysMonth;

        return $this;
    }

    public function incCalculatedPlaysMonth(): self
    {
        if (is_null($this->calculatedPlaysMonth)) {
            $this->calculatedPlaysMonth = 1;
        } else {
            ++$this->calculatedPlaysMonth;
        }

        return $this;
    }

    public function decrCalculatedPlaysMonth(): self
    {
        if ($this->calculatedPlaysMonth < 1) {
            throw new RuntimeException('calculated plays cannot be negative');
        } else {
            --$this->calculatedPlaysMonth;
        }

        return $this;
    }

    public function getOpenTargetPlays(): ?int
    {
        if (is_null($this->getTargetPlays())) {
            return null;
        }

        return $this->getTargetPlays() - $this->getCalculatedPlaysMonth();
    }

    public function getTargetPlays(): ?int
    {
        return $this->targetPlays;
    }

    public function setTargetPlays(int $targetPlays): self
    {
        $this->targetPlays = $targetPlays;

        return $this;
    }

    public function incTargetPlays(): self
    {
        if (is_null($this->targetPlays)) {
            $this->targetPlays = 1;
        } else {
            ++$this->targetPlays;
        }

        return $this;
    }

    public function decrTargetPlays(): self
    {
        if (is_null($this->targetPlays)) {
            $this->targetPlays = -1;
        } else {
            --$this->targetPlays;
        }

        return $this;
    }

    public function getCalculatedSubstitutions(): ?int
    {
        return $this->calculatedSubstitutions;
    }

    public function setCalculatedSubstitutions(?int $calculatedSubstitutions): self
    {
        $this->calculatedSubstitutions = $calculatedSubstitutions;

        return $this;
    }

    public function incCalculatedSubstitutions(): self
    {
        if (is_null($this->calculatedSubstitutions)) {
            $this->calculatedSubstitutions = 1;
        } else {
            ++$this->calculatedSubstitutions;
        }

        return $this;
    }

    public function getOpenSubstitutions(): int
    {
        $calculatedPlaysMonth = intval($this->getCalculatedPlaysMonth());

        return intval($calculatedPlaysMonth / 2) - $this->getCalculatedSubstitutions();
    }

    public function getAdditionalWishes(): ?string
    {
        return $this->additionalWishes;
    }

    public function setAdditionalWishes(?string $additionalWishes): self
    {
        $this->additionalWishes = $additionalWishes;

        return $this;
    }

    public function getScheduledPlaysMonth(): ?int
    {
        return $this->scheduledPlaysMonth;
    }

    public function setScheduledPlaysMonth(?int $scheduledPlaysMonth): self
    {
        $this->scheduledPlaysMonth = $scheduledPlaysMonth;

        return $this;
    }

    public function incScheduledPlaysMonth(): self
    {
        if (is_null($this->scheduledPlaysMonth)) {
            $this->scheduledPlaysMonth = 1;
        } else {
            ++$this->scheduledPlaysMonth;
        }

        return $this;
    }

    public function getScheduledSubstitutions(): ?int
    {
        return $this->scheduledSubstitutions;
    }

    public function setScheduledSubstitutions(?int $scheduledSubstitutions): self
    {
        $this->scheduledSubstitutions = $scheduledSubstitutions;

        return $this;
    }

    public function incScheduledSubstitutions(): self
    {
        if (is_null($this->scheduledSubstitutions)) {
            $this->scheduledSubstitutions = 1;
        } else {
            ++$this->scheduledSubstitutions;
        }

        return $this;
    }

    public function getSoftMaxPlaysWeek(): ?int
    {
        return $this->softMaxPlaysWeek;
    }

    public function setSoftMaxPlaysWeek(?int $softMaxPlaysWeek): static
    {
        $this->softMaxPlaysWeek = $softMaxPlaysWeek;

        return $this;
    }

    public function getSoftMaxPlaysAndSubstitutionsWeek(): ?int
    {
        if (is_null($this->softMaxPlaysWeek)) {
            return null;
        }

        return $this->softMaxPlaysWeek + (int) ceil($this->softMaxPlaysWeek / 2);
    }
}
