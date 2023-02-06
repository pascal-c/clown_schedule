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
    
    public function add(\DateTimeInterface $date, string $daytime, string $key, mixed $entry)
    {
        $this->days[$date->format('d')]->addEntry($daytime, $key, $entry);
    }

    public function getDays(): array
    {
        return array_values($this->days);
    }
}
