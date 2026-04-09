<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class PlayDateBundle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, PlayDate>
     */
    #[ORM\OneToMany(targetEntity: PlayDate::class, mappedBy: 'bundle')]
    #[Assert\Count(min: 2, minMessage: 'Du musst mindestens {{ limit }} Spieltermine auswählen, um sie zu bündeln.')]
    private Collection $playDates;

    public function __construct()
    {
        $this->playDates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, PlayDate>
     */
    public function getPlayDates(): Collection
    {
        return $this->playDates;
    }

    public function addPlayDate(PlayDate $playDate): static
    {
        if (!$this->playDates->contains($playDate)) {
            $this->playDates->add($playDate);
            $playDate->setBundle($this);
        }

        return $this;
    }

    public function removePlayDate(PlayDate $playDate): static
    {
        if ($this->playDates->removeElement($playDate)) {
            // set the owning side to null (unless already changed)
            if ($playDate->getBundle() === $this) {
                $playDate->setBundle(null);
            }
        }

        return $this;
    }
}
