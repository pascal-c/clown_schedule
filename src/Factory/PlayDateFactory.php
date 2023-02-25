<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Daytime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Venue;
use DateTimeImmutable;

class PlayDateFactory extends AbstractFactory
{
    public function create(Month $month, Venue $venue): PlayDate
    {
        $date = $this->generator->dateTimeBetween($month->dbFormat(), $month->next()->dbFormat(), 'Europe/Berlin');
        $playDate = (new PlayDate)
            ->setDate(DateTimeImmutable::createFromMutable($date))
            ->setDaytime(Daytime::getDaytimeOptions()->sample())
            ->setVenue($venue)
            ;

        $this->entityManager->persist($playDate);
        $this->entityManager->flush();
        return $playDate;
    }
}
