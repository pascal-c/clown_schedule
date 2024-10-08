<?php

namespace App\Service\Scheduler;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;

class Result
{
    private Month $month;

    /** @var array<PlayDate> */
    private array $playDates = [];

    /** @var array<int, ?ClownAvailability> */
    private array $clownAvailabilities = [];

    private int $points = 0;

    public static function create(Month $month): static
    {
        return new Result($month);
    }

    private function __construct(Month $month, array $playDates = [], array $clownAvailabilities = [])
    {
        $this->month = $month;
        $this->playDates = $playDates;
        $this->clownAvailabilities = $clownAvailabilities;

        /*foreach($playDates as $playDate) {
            $clownAvailability = $this->clownAvailabilities[$playDate->getId()];
            if (is_null($clownAvailability)) {
                $this->points += self::POINTS_NOT_ASSIGNED;
            } elseif($this->availabilityChecker->maxPlaysWeekExceeded($playDate->getWeek(), $clownAvailability)) {
                $this->points += self::POINTS_CLOWN_MAX_PER_WEEK;
            }elseif(ClownAvailabilityTime::AVAILABILITY_MAYBE === $this->$clownAvailability->getAvailabilityOn($playDate)) {
                $this->points += self::POINTS_CLOWN_MAYBE;
            }
        }*/
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

    public function getAssignedClownAvailability(PlayDate $playDate): ?ClownAvailability
    {
        return $this->clownAvailabilities[$playDate->getId()];
    }

    public function __toString(): string
    {
        $string = '';
        foreach ($this->clownAvailabilities as $playDateId => $clownAvailability) {
            $string .= $playDateId.' '.$clownAvailability?->getClown()?->getName()."\n";
        }

        return $string;
    }

    public function __debugInfo(): array
    {
        $entries = [];
        foreach ($this->clownAvailabilities as $playDateId => $clownAvailability) {
            $entries[] = $playDateId.' '.$clownAvailability?->getClown()?->getName();
        }

        return $entries;
    }
}
