<?php

namespace App\Component;

use App\Entity\Clown;
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

    /** @var array<bool> */
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
            $schedule = $this->scheduleRepository->find($date->getMonth());
            $this->datesScheduled[$key] = is_null($schedule) || $schedule?->isCompleted();
        }
    }
}
