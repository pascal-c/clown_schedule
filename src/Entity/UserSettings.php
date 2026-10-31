<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(options: ['default' => true])]
    public bool $scheduleCompletedEmail = true;

    #[ORM\Column(options: ['default' => true])]
    public bool $openInvoiceOnDashboard = true;

    #[ORM\Column(options: ['default' => false])]
    public bool $openInvoiceEmail = false;

    #[ORM\Column(options: ['default' => true])]
    public bool $openInvoiceEmailReminder = true;

    #[ORM\OneToOne(inversedBy: 'settings', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    public ?Clown $clown = null;
}
