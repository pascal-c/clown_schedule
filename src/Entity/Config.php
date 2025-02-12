<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialPlayDateUrl = null;

    #[ORM\Column]
    private bool $featureMaxPerWeekActive = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecialPlayDateUrl(): ?string
    {
        return $this->specialPlayDateUrl;
    }

    public function setSpecialPlayDateUrl(?string $specialPlayDateUrl): static
    {
        $this->specialPlayDateUrl = $specialPlayDateUrl;

        return $this;
    }

    public function isFeatureMaxPerWeekActive(): bool
    {
        return $this->featureMaxPerWeekActive;
    }

    public function setFeatureMaxPerWeekActive(bool $featureMaxPerWeekActive): static
    {
        $this->featureMaxPerWeekActive = $featureMaxPerWeekActive;

        return $this;
    }
}
