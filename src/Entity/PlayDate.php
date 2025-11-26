<?php

namespace App\Entity;

use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use App\Value\TimeSlotPeriodTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;
use DateTimeInterface;

#[ORM\Entity]
#[UniqueEntity(fields: ['venue', 'date'], message: 'Es existiert bereits ein Spieltermin für diesen Spielort am gleichen Tag.')]
#[ORM\Index(name: 'date_idx', columns: ['date'])]
#[ORM\Index(name: 'type_idx', columns: ['type'])]
class PlayDate implements TimeSlotPeriodInterface
{
    use TimeSlotPeriodTrait;

    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_MOVED = 'moved';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(length: 3)]
    private ?string $daytime = null;

    #[ORM\ManyToOne(inversedBy: 'playDates')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\When(
        expression: 'this.isRegular()',
        constraints: [
            new Assert\NotBlank(),
        ],
    )]
    private ?Venue $venue = null;

    #[ORM\ManyToMany(targetEntity: Clown::class, inversedBy: 'playDates')]
    private Collection $playingClowns;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private bool $isSuper = false;

    #[ORM\OneToMany(mappedBy: 'playDate', targetEntity: PlayDateHistory::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private Collection $playDateHistory;

    #[ORM\OneToMany(mappedBy: 'playDateToGiveOff', targetEntity: PlayDateChangeRequest::class, orphanRemoval: true)]
    #[ORM\OrderBy(['requestedAt' => 'DESC'])]
    private Collection $playDateGiveOffRequests;

    #[ORM\OneToMany(mappedBy: 'PlayDateWanted', targetEntity: PlayDateChangeRequest::class)]
    #[ORM\OrderBy(['requestedAt' => 'DESC'])]
    private Collection $playDateSwapRequests;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $meetingTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $playTimeFrom = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $playTimeTo = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $type = PlayDateType::REGULAR->value;

    #[ORM\ManyToOne(inversedBy: 'playDates')]
    private ?Fee $fee = null;

    #[ORM\ManyToOne(inversedBy: 'playDates')]
    private ?RecurringDate $recurringDate = null;

    #[ORM\Column(length: 100, index: true, options: ['default' => self::STATUS_CONFIRMED])]
    private ?string $status = self::STATUS_CONFIRMED;

    #[ORM\OneToOne(targetEntity: self::class, inversedBy: 'movedFrom', cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?self $movedTo = null;

    #[ORM\OneToOne(mappedBy: 'movedTo', targetEntity: self::class)]
    private ?self $movedFrom = null;

    public function __construct()
    {
        $this->playingClowns = new ArrayCollection();
        $this->playDateHistory = new ArrayCollection();
        $this->playDateGiveOffRequests = new ArrayCollection();
        $this->playDateSwapRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setDaytime(string $daytime): self
    {
        $this->daytime = $daytime;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

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

    public function isTraining(): bool
    {
        return PlayDateType::TRAINING === $this->getType();
    }

    public function isRegular(): bool
    {
        return PlayDateType::REGULAR === $this->getType();
    }

    public function isSpecial(): bool
    {
        return PlayDateType::SPECIAL === $this->getType();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->getTitle() ?? ($this->getVenue() ? $this->getVenue()->getName() : null);
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function isSuper(): bool
    {
        return $this->isSuper;
    }

    public function setIsSuper(bool $isSuper): self
    {
        $this->isSuper = $isSuper;

        return $this;
    }

    /**
     * @return Collection<int, PlayDateHistory>
     */
    public function getPlayDateHistory(): Collection
    {
        return $this->playDateHistory;
    }

    public function addPlayDateHistoryEntry(PlayDateHistory $playDateHistoryEntry): self
    {
        if (!$this->playDateHistory->contains($playDateHistoryEntry)) {
            $this->playDateHistory->add($playDateHistoryEntry);
            $playDateHistoryEntry->setPlayDate($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PlayDateChangeRequest>
     */
    public function getPlayDateGiveOffRequests(): Collection
    {
        return $this->playDateGiveOffRequests;
    }

    public function addPlayDateGiveOffRequest(PlayDateChangeRequest $playDateChangeRequest): self
    {
        if (!$this->playDateGiveOffRequests->contains($playDateChangeRequest)) {
            $this->playDateGiveOffRequests->add($playDateChangeRequest);
            $playDateChangeRequest->setPlayDateToGiveOff($this);
        }

        return $this;
    }

    public function removePlayDateGiveOffRequest(PlayDateChangeRequest $playDateChangeRequest): self
    {
        if ($this->playDateGiveOffRequests->removeElement($playDateChangeRequest)) {
            // set the owning side to null (unless already changed)
            if ($playDateChangeRequest->getPlayDateToGiveOff() === $this) {
                $playDateChangeRequest->setPlayDateToGiveOff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlayDateChangeRequest>
     */
    public function getPlayDateSwapRequests(): Collection
    {
        return $this->playDateSwapRequests;
    }

    public function addPlayDateSwapRequest(PlayDateChangeRequest $playDateSwapRequest): self
    {
        if (!$this->playDateSwapRequests->contains($playDateSwapRequest)) {
            $this->playDateSwapRequests->add($playDateSwapRequest);
            $playDateSwapRequest->setPlayDateWanted($this);
        }

        return $this;
    }

    public function removePlayDateSwapRequest(PlayDateChangeRequest $playDateSwapRequest): self
    {
        if ($this->playDateSwapRequests->removeElement($playDateSwapRequest)) {
            // set the owning side to null (unless already changed)
            if ($playDateSwapRequest->getPlayDateWanted() === $this) {
                $playDateSwapRequest->setPlayDateWanted(null);
            }
        }

        return $this;
    }

    public function getMeetingTime(): ?DateTimeImmutable
    {
        return $this->meetingTime ?? ($this->venue ? $this->venue->getMeetingTime() : null);
    }

    public function setMeetingTime(?DateTimeImmutable $meetingTime): static
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    public function getPlayTimeFrom(): ?DateTimeImmutable
    {
        return $this->playTimeFrom ?? ($this->venue ? $this->venue->getPlayTimeFrom() : null);
    }

    public function setPlayTimeFrom(?DateTimeImmutable $playTimeFrom): static
    {
        $this->playTimeFrom = $playTimeFrom;

        return $this;
    }

    public function getPlayTimeTo(): ?DateTimeImmutable
    {
        return $this->playTimeTo ?? ($this->venue ? $this->venue->getPlayTimeTo() : null);
    }

    public function setPlayTimeTo(?DateTimeImmutable $playTimeTo): static
    {
        $this->playTimeTo = $playTimeTo;

        return $this;
    }

    public function getType(): PlayDateType
    {
        return PlayDateType::from($this->type);
    }

    public function setType(PlayDateType $type): static
    {
        $this->type = $type->value;

        return $this;
    }

    public function getVenueFee(): ?Fee
    {
        return $this->getVenue()?->getFeeFor($this->getDate());
    }

    public function hasVenueFee(): bool
    {
        return !is_null($this->getVenueFee()?->getId());
    }

    public function getFee(): ?Fee
    {
        return $this->getPlayDateFee() ?? $this->getVenueFee();
    }

    public function hasFee(): bool
    {
        return $this->hasIndividualFee() || $this->hasVenueFee();
    }

    public function getPlayDateFee(): ?Fee
    {
        return $this->fee;
    }

    public function hasIndividualFee(): bool
    {
        return !is_null($this->getPlayDateFee()?->getId());
    }

    public function setFee(?Fee $fee): static
    {
        $this->fee = $fee;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->isRegular() || $this->isSpecial();
    }

    public function getRecurringDate(): ?RecurringDate
    {
        return $this->recurringDate;
    }

    public function isRecurring(): bool
    {
        return !is_null($this->recurringDate);
    }

    public function setRecurringDate(?RecurringDate $recurringDate): static
    {
        $this->recurringDate = $recurringDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function cancel(): static
    {
        $this->status = static::STATUS_CANCELLED;

        return $this;
    }

    public function move(PlayDate $playDate): static
    {
        $this->status = static::STATUS_MOVED;
        $this->movedTo = $playDate;

        return $this;
    }

    public function isConfirmed(): bool
    {
        return self::STATUS_CONFIRMED === $this->status;
    }

    public function isCancelled(): bool
    {
        return self::STATUS_CANCELLED === $this->status;
    }

    public function isMoved(): bool
    {
        return self::STATUS_MOVED === $this->status;
    }

    public function getMovedTo(): ?self
    {
        return $this->movedTo;
    }

    public function setMovedTo(?self $movedTo): static
    {
        $this->movedTo = $movedTo;

        return $this;
    }

    public function getMovedFrom(): ?self
    {
        return $this->movedFrom;
    }
}
