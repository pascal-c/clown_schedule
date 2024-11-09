<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use Countable;

class Result implements Countable
{
    private Month $month;

    /** @var array<PlayDate> */
    private array $playDates = [];

    /** @var array<int, ?ClownAvailability> */
    private array $clownAvailabilities = [];

    private ?int $points = null;

    public static function create(Month $month): static
    {
        return new Result($month);
    }

    private function __construct(Month $month, array $playDates = [], array $clownAvailabilities = [])
    {
        $this->month = $month;
        $this->playDates = $playDates;
        $this->clownAvailabilities = $clownAvailabilities;
    }

    public function getMonth(): Month
    {
        return $this->month;
    }

    public function add(PlayDate $playDate, ?ClownAvailability $clownAvailability): static
    {
        $playDates = $this->playDates;
        $playDates[] = $playDate;

        $clownAvailabilities = $this->clownAvailabilities;
        $clownAvailabilities[$playDate->getId()] = $clownAvailability;

        return new Result($this->month, $playDates, $clownAvailabilities);
    }

    public function getPlayDates(): array
    {
        return $this->playDates;
    }

    public function count(): int
    {
        return count($this->playDates);
    }

    public function getAssignedClownAvailability(PlayDate $playDate): ?ClownAvailability
    {
        return $this->clownAvailabilities[$playDate->getId()];
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }
}
