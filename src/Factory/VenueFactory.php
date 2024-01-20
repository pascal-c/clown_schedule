<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Venue;
use App\Lib\Collection;
use DateTimeImmutable;

class VenueFactory extends AbstractFactory
{
    public function create(
        string $name = null,
        array $playingClowns = [],
        string $daytimeDefault = null,
        string $meetingTime = null,
        string $playTimeFrom = null,
        string $playTimeTo = null,
        float $feeByPublicTransport = null,
        float $feeByCar = null,
        int $kilometers = null,
        float $feePerKilometer = 0.35,
        bool $isSuper = false,
    ): Venue {
        list($daytimeDefaultGenerated, $meetingTimeGenerated, $playTimeFromGenerated, $playTimeToGenerated) = $this->timeOptions()->sample();
        $venue = (new Venue())
            ->setName($this->generateName($name))
            ->setDaytimeDefault($daytimeDefault ?? $daytimeDefaultGenerated)
            ->setMeetingTime(new DateTimeImmutable($meetingTime ?? $meetingTimeGenerated))
            ->setPlayTimeFrom(new DateTimeImmutable($playTimeFrom ?? $playTimeFromGenerated))
            ->setPlayTimeTo(new DateTimeImmutable($playTimeTo ?? $playTimeToGenerated))
            ->setFeeByPublicTransport($feeByPublicTransport)
            ->setFeeByCar($feeByCar)
            ->setKilometers($kilometers)
            ->setFeePerKilometer($feePerKilometer)
            ->setIsSuper($isSuper)
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
            ['am', '08:30', '09:00', '11:00'],
            ['am', '09:00', '09:30', '12:00'],
            ['pm', '14:30', '15:00', '17:00'],
            ['pm', '15:00', '15:30', '18:00'],
            ['all', '11:00', '12:30', '16:00'],
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
