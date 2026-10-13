<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'clown_month_index', fields: ['clown', 'month'])]
class Invoice
{
    public const string STATUS_OPEN = 'invoice-status-open';
    public const string STATUS_ISSUED = 'invoice-status-issued';
    public const string STATUS_PAID = 'invoice-status-paid';
    public const array STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_ISSUED,
        self::STATUS_PAID,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private Clown $clown;

    #[ORM\Column(length: 7)]
    private string $month;

    #[ORM\Column(length: 100)]
    private string $status = self::STATUS_OPEN;

    public function __construct(Month $month, Clown $clown)
    {
        $this->setMonth($month);
        $this->clown = $clown;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClown(): Clown
    {
        return $this->clown;
    }

    public function setClown(Clown $clown): static
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid status "%s". Valid statuses are: %s', $status, implode(', ', self::STATUSES)));
        }
        $this->status = $status;

        return $this;
    }

    public function isOpen(): bool
    {
        return self::STATUS_OPEN === $this->status;
    }

    public function isIssued(): bool
    {
        return self::STATUS_ISSUED === $this->status;
    }

    public function isPaid(): bool
    {
        return self::STATUS_PAID === $this->status;
    }
}
