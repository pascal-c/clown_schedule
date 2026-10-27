<?php

namespace App\Entity;

use App\Value\NotificationType;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'notification_type_clown_month_index', fields: ['type', 'clown', 'month'])]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255, type: 'string', enumType: NotificationType::class)]
    public NotificationType $type = NotificationType::INVOICE;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    public ?Clown $clown = null;

    #[ORM\Column]
    public ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 7, nullable: true)]
    public ?string $month = null;

    public function getMonth(): ?Month
    {
        if (is_null($this->month)) {
            return null;
        }

        return Month::build($this->month);
    }

    public function setMonth(?Month $month): self
    {
        $this->month = $month?->getKey();

        return $this;
    }
}
