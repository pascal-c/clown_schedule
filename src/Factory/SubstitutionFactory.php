<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\Substitution;
use App\Lib\Collection;
use App\Value\TimeSlotInterface;
use DateTimeImmutable;

class SubstitutionFactory extends AbstractFactory
{
    public function create(DateTimeImmutable $date = null, string $daytime = null, Clown $clown = null): Substitution
    {
        $date ??= new DateTimeImmutable();
        $daytime ??= (new Collection(TimeSlotInterface::DAYTIMES))->sample();

        $substitution = (new Substitution())
            ->setDate($date)
            ->setDaytime($daytime)
            ->setSubstitutionClown($clown)
        ;

        $this->entityManager->persist($substitution);
        $this->entityManager->flush();

        return $substitution;
    }
}
