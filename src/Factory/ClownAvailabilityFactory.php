<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;

class ClownAvailabilityFactory extends AbstractFactory
{
    public function create(Clown $clown, Month $month): ClownAvailability
    {
        $wishedPlaysMonth = rand(2, 10);
        $clownAvailability = (new ClownAvailability)
            ->setClown($clown)
            ->setMonth($month)
            ->setMaxPlaysMonth($wishedPlaysMonth + rand(0, 5))
            ->setWishedPlaysMonth($wishedPlaysMonth)
            ->setMaxPlaysDay(rand(1, 2))
            ->setAdditionalWishes($this->generator->optional()->text(100))
            ;
        foreach ($month->days() as $date) {
            foreach (['am', 'pm'] as $daytime) {
                $timeSlot = (new ClownAvailabilityTime)
                    ->setClown($clown)
                    ->setDate($date)
                    ->setDaytime($daytime)
                    ->setAvailability(['yes', 'yes', 'yes', 'maybe', 'no', 'no'][mt_rand(0, 5)]);
                $clownAvailability->addClownAvailabilityTime($timeSlot);
                $this->entityManager->persist($timeSlot);
            }
        }

        $this->entityManager->persist($clownAvailability);
        $this->entityManager->flush();
        return $clownAvailability;
    }
}
