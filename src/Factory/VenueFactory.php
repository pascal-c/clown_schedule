<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Venue;
use App\Lib\Collection;

class VenueFactory extends AbstractFactory
{
    public function create(string $name = null, array $playingClowns = [], string $daytimeDefault = null)
    {
        list($daytimeDefault, $meetingTime, $playTimeFrom, $playTimeTo) = $this->timeOptions()->sample();
        $venue = (new Venue())
            ->setName($this->generateName($name))
            ->setDaytimeDefault($daytimeDefault)
            ->setMeetingTime($meetingTime)
            ->setPlayTimeFrom($playTimeFrom)
            ->setPlayTimeTo($playTimeTo)
        ;
        foreach ($playingClowns as $clown) {
            $venue->addResponsibleClown($clown);
        }

        $this->entityManager->persist($venue);
        $this->entityManager->flush();

        return $venue;
    }

    private function timeOptions(): Collection
    {
        return new Collection([
            ['am', new \DateTimeImmutable('08:30'), new \DateTimeImmutable('09:00'), new \DateTimeImmutable('11:00')],
            ['am', new \DateTimeImmutable('09:00'), new \DateTimeImmutable('09:30'), new \DateTimeImmutable('12:00')],
            ['pm', new \DateTimeImmutable('14:30'), new \DateTimeImmutable('15:00'), new \DateTimeImmutable('17:00')],
            ['pm', new \DateTimeImmutable('15:00'), new \DateTimeImmutable('15:30'), new \DateTimeImmutable('18:00')],
            ['all', new \DateTimeImmutable('11:00'), new \DateTimeImmutable('12:30'), new \DateTimeImmutable('16:00')],
        ]);
    }

    private function generateName(?string $name): string
    {
        if (!is_null($name)) {
            return $name;
        }

        $prefix = (new Collection(['Klinik', 'Seniorenheim', 'Senior:innenparadies']))->sample();

        return $prefix.' '.$this->generate('city', $name);
    }
}
