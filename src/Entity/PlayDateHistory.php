<?php

namespace App\Entity;

use App\Value\PlayDateChangeReason;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
class PlayDateHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'playDateHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlayDate $playDate = null;

    #[ORM\ManyToMany(targetEntity: Clown::class)]
    private Collection $playingClowns;

    #[ORM\Column]
    private ?DateTimeImmutable $changedAt = null;

    #[ORM\ManyToOne]
    private ?Clown $changedBy = null;

    #[ORM\Column(length: 100)]
    private ?string $reason = null;

    public function __construct()
    {
        $this->playingClowns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayDate(): ?PlayDate
    {
        return $this->playDate;
    }

    public function setPlayDate(PlayDate $PlayDate): self
    {
        $this->playDate = $PlayDate;

        return $this;
    }

    /**
     * @return Collection<int, Clown>
     */
    public function getPlayingClowns(): Collection
    {
        return $this->playingClowns;
    }

    public function addPlayingClown(Clown $playingClown): self
    {
        if (!$this->playingClowns->contains($playingClown)) {
            $this->playingClowns->add($playingClown);
        }

        return $this;
    }

    public function removePlayingClown(Clown $playingClown): self
    {
        $this->playingClowns->removeElement($playingClown);

        return $this;
    }

    public function getChangedAt(): ?DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function setChangedAt(DateTimeImmutable $changedAt): self
    {
        $this->changedAt = $changedAt;

        return $this;
    }

    public function getChangedBy(): ?Clown
    {
        return $this->changedBy;
    }

    public function setChangedBy(?Clown $changedBy): self
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    public function getReason(): PlayDateChangeReason
    {
        return PlayDateChangeReason::from($this->reason);
    }

    public function setReason(PlayDateChangeReason $reason): self
    {
        $this->reason = $reason->value;

        return $this;
    }
}
