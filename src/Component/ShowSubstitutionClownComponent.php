<?php
namespace App\Component;

use App\Entity\Clown;
use App\Entity\TimeSlot;
use App\Repository\TimeSlotRepository;
use App\Service\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('show_substitution_clown')]
final class ShowSubstitutionClownComponent
{
    public ?TimeSlot $timeSlot;
    public ?Clown $currentClown;

    public function __construct(private TimeSlotRepository $timeSlotRepository, private AuthService $authService) {}

    public function mount(\DateTimeImmutable $date, string $daytime) {
        $this->timeSlot = $this->timeSlotRepository->find($date, $daytime);
        if (is_null($this->timeSlot)) {
            $this->timeSlot = new TimeSlot;
            $this->timeSlot->setDate($date)->setDaytime($daytime);
        }

        $this->currentClown = $this->authService->getCurrentClown();
    }

}
