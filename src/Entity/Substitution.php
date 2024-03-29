<?php

namespace App\Entity;

use App\Value\TimeSlotPeriodInterface;
use App\Value\TimeSlotPeriodTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'timeslot_date_daytime_index', fields: ['date', 'daytime'])]
class Substitution implements TimeSlotPeriodInterface
{
    use TimeSlotPeriodTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 7)]
    private ?string $month = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $date = null;

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
        return Month::build($this->month);
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;
        $this->month = (new Month($date))->getKey();

        return $this;
    }

    public function setDaytime(string $daytime): self
    {
        $this->daytime = $daytime;

        return $this;
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
