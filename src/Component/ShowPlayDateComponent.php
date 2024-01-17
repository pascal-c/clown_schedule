<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use App\Service\AuthService;
use App\Value\ScheduleStatus;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('show_play_date')]
final class ShowPlayDateComponent
{
    public ?PlayDate $playDate;
    public ?Clown $currentClown;
    public string $colorClass = '';
    public bool $showClowns = true;

    public function __construct(private AuthService $authService, private ScheduleRepository $scheduleRepository)
    {
    }

    public function mount(PlayDate $playDate, Month $month): void
    {
        $this->currentClown = $this->authService->getCurrentClown();
        $this->playDate = $playDate;

        $schedule = $this->scheduleRepository->find($month);
        $this->colorClass = $this->getColorClass($playDate, $schedule);
        $this->showClowns = $this->currentClown->isAdmin() || ScheduleStatus::COMPLETED === $schedule?->getStatus();
    }

    private function getColorClass(PlayDate $playDate, ?Schedule $schedule): string
    {
        if ($playDate->isSpecial()) {
            return 'text-secondary';
        }

        if (null === $schedule || 2 === $playDate->getPlayingClowns()->count()) {
            return 'text-dark';
        }

        return 'text-danger';
    }
}
