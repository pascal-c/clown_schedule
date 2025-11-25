<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Repository\ConfigRepository;
use App\Repository\ScheduleRepository;
use App\Service\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('show_play_date')]
final class ShowPlayDateComponent
{
    public ?PlayDate $playDate;
    public ?Clown $currentClown;
    public string $colorClass = '';
    public bool $showClowns = true;
    public string $specialPlayDateUrl = '';
    public bool $showNotEnonoughClownsWarning = false;

    public function __construct(
        private AuthService $authService,
        private ScheduleRepository $scheduleRepository,
        private ConfigRepository $configRepository,
    ) {
    }

    public function mount(PlayDate $playDate, Month $month): void
    {
        $this->currentClown = $this->authService->getCurrentClown();
        $this->playDate = $playDate;
        $this->specialPlayDateUrl = $playDate->isSpecial() ? $this->configRepository->find()->getSpecialPlayDateUrl() : '';

        $schedule = $this->scheduleRepository->find($month);
        $this->colorClass = $this->getColorClass($playDate, $schedule);
        $this->showClowns = $this->currentClown->isAdmin() || is_null($schedule) || $schedule?->isCompleted();
        $this->showNotEnonoughClownsWarning = $schedule && $playDate->getPlayingClowns()->count() < 2;
    }

    private function getColorClass(PlayDate $playDate, ?Schedule $schedule): string
    {
        if ($playDate->isSpecial()) {
            return 'text-secondary';
        } elseif ($playDate->isTraining()) {
            return 'text-secondary text-opacity-75';
        }

        return 'text-dark';
    }
}
