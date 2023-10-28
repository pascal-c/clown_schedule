<?php

namespace App\Entity;

use App\Value\ScheduleStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity('month')]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 7, unique: true)]
    private ?string $month = null;

    #[ORM\Column(length: 100)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonth(): Month
    {
        return Month::build($this->month);
    }

    public function setMonth(Month $month): self
    {
        $this->month = $month->getKey();

        return $this;
    }

    public function getStatus(): ScheduleStatus
    {
        return ScheduleStatus::from($this->status ?? ScheduleStatus::NOT_STARTED->value);
    }

    public function setStatus(ScheduleStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    public function isCompleted(): bool
    {
        return ScheduleStatus::COMPLETED === $this->getStatus();
    }
}
