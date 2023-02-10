<?php

namespace App\ViewModel;

class Schedule
{
    private array $days = [];

    public function add(\DateTimeInterface $date, string $daytime, string $key, mixed $entry)
    {
        $this->days[$date->format('d')]->addEntry($daytime, $key, $entry);
    }

    public function getDays(): array
    {
        return array_values($this->days);
    }

    public function setDays(array $days): self
    {
        $this->days = $days;

        return $this;
    }
}
