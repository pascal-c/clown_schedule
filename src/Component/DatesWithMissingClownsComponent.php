<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Guard\PlayDateGuard;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Service\TimeService;
use App\Value\ScheduleStatus;
use App\ViewController\PlayDateViewController;
use DateTimeImmutable;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dates_with_missing_clowns')]
class DatesWithMissingClownsComponent
{
    public Clown $currentClown;
    public array $dates = [];
    public DateTimeImmutable $until;
    public ?string $message = null;

    public function __construct(
        private PlayDateRepository $playDateRepository,
        private ScheduleRepository $scheduleRepository,
        private ConfigRepository $configRepository,
        private TimeService $timeService,
        private PlayDateGuard $playDateGuard,
        private PlayDateViewController $playDateViewController,
    ) {
    }

    public function mount(Clown $currentClown)
    {
        $this->currentClown = $currentClown;
        $this->message = $this->message();
        $this->until = $this->until();

        $playDates = $this->playDateRepository->futurePlayDatesWithMissingClowns($this->until);
        $playDates = array_filter(
            $playDates,
            fn (PlayDate $date): bool => $this->playDateGuard->canAssign($date)
        );
        $this->dates = array_map(
            fn (PlayDate $playDate) => $this->playDateViewController->getPlayDateViewModel($playDate, $this->currentClown),
            $playDates
        );
    }

    public function until(): DateTimeImmutable
    {
        if ($this->configRepository->isFeatureCalculationActive()) {
            $month = new Month($this->timeService->firstOfNextMonth());
            $schedule = $this->scheduleRepository->find($month) ?? new Schedule();

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

    private function message(): ?string
    {
        $month = new Month($this->timeService->firstOfNextMonth());
        if ($this->currentClown->isAdmin() && $this->configRepository->isFeatureCalculationActive() && !$this->scheduleRepository->find($month)?->isCompleted()) {
            return 'ACHTUNG! Die Spielplanberechnung für den Monat '.$month->getLabel().' wurde noch nicht abgeschlossen.';
        }

        return null;
    }
}
