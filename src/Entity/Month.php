<?php

namespace App\Entity;

class Month
{
    private \DateTimeImmutable $date;

    public function __construct(\DateTimeImmutable $date) {
        $this->date = $date->modify('first day of midnight');
    }

    public function days()
    {
        $interval = new \DateInterval('P1D');
        return new \DatePeriod($this->date, $interval, $this->next()->date);
    }

    public function getKey(): string
    {
        return $this->date->format('Y-m');
    }

    public function getLabel(): string
    {
        $formatter = new \IntlDateFormatter(
            'de_DE', 
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Europe/Berlin',
            \IntlDateFormatter::GREGORIAN,
            'MMM y');
        return $formatter->format($this->date);
        #return $this->date->format('M Y');
    }

    public function dbFormat(): string
    {
        return $this->date->format('Y-m-01');
    }

    public function next(): Month
    {
        $interval = new \DateInterval('P1M');
        return new Month($this->date->add($interval));
    }
}
