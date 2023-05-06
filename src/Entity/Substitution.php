<?php

namespace App\Entity;

use App\Value\TimeSlot;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'timeslot_date_daytime_index', fields: ['date', 'daytime'])]
class Substitution implements TimeSlotPeriodInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 7)]
    private ?string $month = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 2)]
    private ?string $daytime = null;

    #[ORM\ManyToOne(inversedBy: 'substitutionTimeSlots')]
    private ?Clown $substitutionClown = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonth(): Month
    {
        return new Month(new \DateTimeImmutable($this->month));
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        $this->month = (new Month($date))->getKey();

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

    public function getSubstitutionClown(): ?Clown
    {
        return $this->substitutionClown;
    }

    public function setSubstitutionClown(?Clown $substitutionClown): self
    {
        $this->substitutionClown = $substitutionClown;

        return $this;
    }
}
