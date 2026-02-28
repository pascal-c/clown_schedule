<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Service\TimeService;
use App\Service\VenueService;
use App\Value\ScheduleStatus;
use DateTimeImmutable;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dates_with_missing_clowns')]
class DatesWithMissingClownsComponent
{
    public Clown $currentClown;
    public array $dates = [];

    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ScheduleRepository $scheduleRepository,
        private ConfigRepository $configRepository,
        private TimeService $timeService,
        private VenueService $venueService,
    ) {
    }

    public function mount(Clown $currentClown)
    {
        $this->currentClown = $currentClown;
        $this->dates = $this->playDateRepository->futurePlayDatesWithMissingClowns($this->until());

        if (!$this->currentClown->isAdmin()) {
            $this->dates = array_filter(
                $this->dates,
                fn (PlayDate $date): bool => $this->venueService->getTeam($date->getVenue())->contains($currentClown)
            );
        }
    }

    private function until(): DateTimeImmutable
    {
        if ($this->configRepository->isFeatureCalculationActive()) {
            $this->scheduleRepository->find(new Month($this->timeService->firstOfNextMonth()));
            $schedule = $this->scheduleRepository->find(new Month($this->timeService->firstOfNextMonth())) ?? new Schedule();

            return match($schedule->getStatus()) {
                ScheduleStatus::NOT_STARTED => $this->timeService->firstOfNextMonth(),
                default => $this->timeService->firstOfMonthAfterNext(),
            };
        }

        return match(true) {
            intval($this->timeService->now()->format('d')) < 15 => $this->timeService->firstOfNextMonth(),
            default => $this->timeService->firstOfMonthAfterNext(),
        };
    }
}
