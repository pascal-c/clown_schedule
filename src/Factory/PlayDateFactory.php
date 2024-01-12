<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Venue;
use DateTimeImmutable;
use Symfony\Contracts\Service\Attribute\Required;

class PlayDateFactory extends AbstractFactory
{
    protected VenueFactory $venueFactory;

    #[Required]
    public function _inject(VenueFactory $venueFactory)
    {
        $this->venueFactory = $venueFactory;
    }

    public function create(Month $month = null, DateTimeImmutable $date = null, string $daytime = null, Venue $venue = null, array $playingClowns = [], $isSpecial = false, $title = null): PlayDate
    {
        $date ??= DateTimeImmutable::createFromMutable(
            $this->generator->dateTimeBetween($month->dbFormat(), $month->next()->dbFormat(), 'Europe/Berlin')
        );
        $venue ??= $this->venueFactory->create();
        $playDate = (new PlayDate())
            ->setDate($date)
            ->setDaytime($daytime ?? $venue->getDaytimeDefault())
            ->setVenue($venue)
            ->setIsSpecial($isSpecial)
            ->setTitle($title)
        ;
        foreach ($playingClowns as $playingClown) {
            $playDate->addPlayingClown($playingClown);
        }

        $this->entityManager->persist($playDate);
        $this->entityManager->flush();

        return $playDate;
    }
}
