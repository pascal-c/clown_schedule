<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Value\TimeSlotInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('next_dates_per_clown')]
class NextDatesPerClownComponent
{
    public Clown $currentClown;
    public array $dates = [];

    public function __construct(
        private PlayDateRepository $playDateRepository,
        private SubstitutionRepository $substitutionRepository,
        private ScheduleRepository $scheduleRepository,
    ) {
    }

    public function mount(Clown $currentClown)
    {
        $playDates = $this->playDateRepository->futureByClown($currentClown);
        $substitutions = $this->substitutionRepository->futureByClown($currentClown);
        $this->currentClown = $currentClown;
        $dateEntities = array_merge($playDates, $substitutions);
        usort(
            $dateEntities,
            fn (TimeSlotInterface $a, TimeSlotInterface $b) => $a->getDate() == $b->getDate()
                ?
                $a->getDaytime() <=> $b->getDaytime()
                :
                $a->getDate() <=> $b->getDate()
        );

        foreach ($dateEntities as $dateEntity) {
            $date = ['dateEntity' => $dateEntity];
            if ($dateEntity instanceof PlayDate && $dateEntity->isCancelled()) {
                $date['class'] = 'text-muted';
                $date['title'] = 'Spieltermin abgesagt';
            } elseif ($dateEntity instanceof PlayDate && $dateEntity->isMoved()) {
                $date['class'] = 'text-muted';
                $date['title'] = 'Spieltermin verschoben';
            } elseif ($dateEntity instanceof PlayDate && $dateEntity->isTraining() && !$dateEntity->getPlayingClowns()->contains($this->currentClown)) {
                $date['class'] = 'text-muted';
                $date['title'] = 'Du bist nicht angemeldet';
            } else {
                $schedule = $this->scheduleRepository->find($dateEntity->getMonth());
                if ($schedule && !$schedule->isCompleted()) {
                    $date['class'] = 'text-muted';
                    $date['title'] = 'Termin noch unsicher!';
                }
            }

            $this->dates[] = $date;
        }
    }
}
