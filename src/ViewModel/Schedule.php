<?php

namespace App\ViewModel;

use App\Entity\Month;
use App\ViewModel\Day;

class Schedule
{
    private array $days = [];

    public function __construct(Month $month)
    {
        foreach($month->days() as $day) {
            $this->days[$day->format('d')] = new Day($day);
        }
    }
    
    public function add(\DateTimeInterface $date, string $daytime, $entry)
    {
        $this->days[$date->format('d')]->addEntry($daytime, $entry);
    }

    public function getDays(): array
    {
        return array_values($this->days);
    }
}
