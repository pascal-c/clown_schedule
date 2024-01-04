<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Substitution;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\AuthService;
use App\Value\ScheduleStatus;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use DateTimeImmutable;

#[AsTwigComponent('show_substitution_clown')]
final class ShowSubstitutionClownComponent
{
    public ?Substitution $substitution;
    public ?Clown $currentClown;
    public string $colorClass = '';
    public bool $showIt = true;

    public function __construct(
        private SubstitutionRepository $substitutionRepository,
        private ScheduleRepository $scheduleRepository,
        private AuthService $authService,
    ) {
    }

    public function mount(DateTimeImmutable $date, string $daytime, Month $month): void
    {
        $this->substitution = $this->substitutionRepository->find($date, $daytime);
        if (is_null($this->substitution)) {
            $this->substitution = new Substitution();
            $this->substitution->setDate($date)->setDaytime($daytime);
        }

        $this->currentClown = $this->authService->getCurrentClown();

        $schedule = $this->scheduleRepository->find($month);
        $this->colorClass = $schedule && !$this->substitution->getSubstitutionClown() ? 'text-danger' : '';
        $this->showIt = $this->currentClown->isAdmin() || ScheduleStatus::COMPLETED === $schedule?->getStatus();
    }
}
