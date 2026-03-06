<?php

namespace App\Entity;

use App\Value\PlayDateChangeRequestStatus;
use App\Value\PlayDateChangeRequestType;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
class PlayDateChangeRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'playDateGiveOffRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlayDate $playDateToGiveOff = null;

    #[ORM\ManyToOne(inversedBy: 'playDateSwapRequests')]
    private ?PlayDate $PlayDateWanted = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clown $requestedBy = null;

    #[ORM\ManyToOne]
    private ?Clown $requestedTo = null;

    #[ORM\Column(length: 100)]
    private string $status = PlayDateChangeRequestStatus::WAITING->value;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column]
    private ?DateTimeImmutable $requestedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayDateToGiveOff(): ?PlayDate
    {
        return $this->playDateToGiveOff;
    }

    public function setPlayDateToGiveOff(?PlayDate $playDateToGiveOff): self
    {
        $this->playDateToGiveOff = $playDateToGiveOff;

        return $this;
    }

    public function getPlayDateWanted(): ?PlayDate
    {
        return $this->PlayDateWanted;
    }

    public function setPlayDateWanted(?PlayDate $PlayDateWanted): self
    {
        $this->PlayDateWanted = $PlayDateWanted;

        return $this;
    }

    public function getRequestedBy(): ?Clown
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(?Clown $requestedBy): self
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }

    public function getRequestedTo(): ?Clown
    {
        return $this->requestedTo;
    }

    public function setRequestedTo(?Clown $requestedTo): self
    {
        $this->requestedTo = $requestedTo;

        return $this;
    }

    public function getStatus(): PlayDateChangeRequestStatus
    {
        return PlayDateChangeRequestStatus::from($this->status);
    }

    public function setStatus(PlayDateChangeRequestStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    public function isAccepted(): bool
    {
        return PlayDateChangeRequestStatus::ACCEPTED === $this->getStatus();
    }

    public function isWaiting(): bool
    {
        return PlayDateChangeRequestStatus::WAITING === $this->getStatus();
    }

    public function getType(): PlayDateChangeRequestType
    {
        return PlayDateChangeRequestType::from($this->type);
    }

    public function setType(PlayDateChangeRequestType $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    public function isSwap(): bool
    {
        return PlayDateChangeRequestType::SWAP === $this->getType();
    }

    public function isGiveOff(): bool
    {
        return PlayDateChangeRequestType::GIVE_OFF === $this->getType();
    }

    public function isTakeOver(): bool
    {
        return PlayDateChangeRequestType::TAKE_OVER === $this->getType();
    }

    public function getRequestedAt(): ?DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(DateTimeImmutable $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function canAccept(Clown $clown): bool
    {
        return null === $this->requestedTo || $this->requestedTo === $clown;
    }

    public function canDecline(Clown $clown): bool
    {
        return $this->canAccept($clown);
    }

    public function canCancel(Clown $clown): bool
    {
        return $this->requestedBy === $clown;
    }

    public function isValid(): bool
    {
        if (!$this->playDateToGiveOff->isConfirmed()) {
            return false;
        }

        return match ($this->getType()) {
            PlayDateChangeRequestType::GIVE_OFF => $this->isValidGiveOff(),
            PlayDateChangeRequestType::TAKE_OVER => $this->isValidTakeOver(),
            PlayDateChangeRequestType::SWAP => $this->isValidSwap(),
        };
    }

    private function isValidGiveOff(): bool
    {
        return $this->playDateToGiveOff->getPlayingClowns()->contains($this->requestedBy);
    }

    private function isValidTakeOver(): bool
    {
        return count($this->playDateToGiveOff->getPlayingClowns()) < $this->playDateToGiveOff->getNeededClowns();
    }

    private function isValidSwap(): bool
    {
        return $this->playDateToGiveOff->getPlayingClowns()->contains($this->requestedBy)
            && $this->PlayDateWanted->getPlayingClowns()->contains($this->requestedTo)
            && $this->PlayDateWanted->isConfirmed();
    }
}
