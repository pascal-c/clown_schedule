<?php

namespace App\Entity;

use App\Value\CalendarType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'calendar_clown_type_index', fields: ['clown', 'type'])]
class Calendar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'calendars')]
    #[ORM\JoinColumn(nullable: false)]
    private Clown $clown;

    #[ORM\Column(type: Types::GUID)]
    private string $uuid;

    #[ORM\Column(length: 100)]
    private string $type = CalendarType::PERSONAL->value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClown(): ?Clown
    {
        return $this->clown;
    }

    public function setClown(?Clown $clown): static
    {
        $this->clown = $clown;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getType(): CalendarType
    {
        return CalendarType::from($this->type);
    }

    public function setType(CalendarType $type): static
    {
        $this->type = $type->value;

        return $this;
    }

    public function isPersonal(): bool
    {
        return CalendarType::PERSONAL->value === $this->type;
    }

    public function isFull(): bool
    {
        return CalendarType::ALL->value === $this->type;
    }
}
