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

    /** @var array<TimeSlotInterface> */
    public array $dates = [];

    public array $datesScheduled = [];

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
        $this->dates = array_merge($playDates, $substitutions);
        usort(
            $this->dates,
            fn (TimeSlotInterface $a, TimeSlotInterface $b) => $a->getDate() == $b->getDate()
                ?
                $a->getDaytime() <=> $b->getDaytime()
                :
                $a->getDate() <=> $b->getDate()
        );

        foreach ($this->dates as $key => $date) {
            if ($date instanceof PlayDate && $date->isCancelled()) {
                $this->datesScheduled[$key]['class'] = 'text-muted';
                $this->datesScheduled[$key]['title'] = 'Spieltermin abgesagt';
            } elseif ($date instanceof PlayDate && $date->isMoved()) {
                $this->datesScheduled[$key]['class'] = 'text-muted';
                $this->datesScheduled[$key]['title'] = 'Spieltermin verschoben';
            } else {
                $schedule = $this->scheduleRepository->find($date->getMonth());
                if ($schedule && !$schedule->isCompleted()) {
                    $this->datesScheduled[$key]['class'] = 'text-muted';
                    $this->datesScheduled[$key]['title'] = 'Termin noch unsicher!';
                }
            }
        }
    }
}
