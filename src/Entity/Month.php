<?php

declare(strict_types=1);

namespace App\Entity;

class Month
{
    private \DateTimeImmutable $date;

    public function __construct(\DateTimeImmutable $date)
    {
        $this->date = $date->modify('first day of midnight');
    }

    public static function build(string $dateString): self
    {
        return new Month(new \DateTimeImmutable($dateString));
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

    public function getYear(): string
    {
        return $this->date->format('Y');
    }

    public function getLabel(): string
    {
        $formatter = new \IntlDateFormatter(
            'de_DE',
            timezone: 'Europe/Berlin',
            pattern: 'MMM y'
        );

        return $formatter->format($this->date);
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

    public function previous(): Month
    {
        return new Month($this->date->modify('-1 month'));
    }
}
