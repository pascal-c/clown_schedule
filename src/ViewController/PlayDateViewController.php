<?php

namespace App\ViewController;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Repository\ConfigRepository;
use App\Repository\SubstitutionRepository;
use App\Service\PlayDateChangeRequestCloseInvalidService;
use App\Service\TimeService;
use App\ViewModel\PlayDate as PlayDateViewModel;

class PlayDateViewController
{
    public function __construct(private SubstitutionRepository $substitutionRepository, private ConfigRepository $configRepository, private TimeService $timeService)
    {
    }

    public function getPlayDateViewModel(PlayDate $playDate, Clown $currentClown): PlayDateViewModel
    {
        $substitutionClowns = array_map(
            fn (Substitution $substitution) => $substitution->getSubstitutionClown(),
            $this->substitutionRepository->findByTimeSlotPeriod($playDate),
        );
        $specialPlayDateUrl = $playDate->isSpecial() ? $this->configRepository->find()->getSpecialPlayDateUrl() : '';

        return new PlayDateViewModel(
            playDate: $playDate,
            substitutionClowns: $substitutionClowns,
            specialPlayDateUrl: $specialPlayDateUrl,
            showChangeRequestLink: $this->getShowChangeRequestLink($playDate, $currentClown),
            showRegisterForTrainingLink: $this->showRegisterForTrainingLink($playDate),
        );
    }

    private function showRegisterForTrainingLink(PlayDate $playDate): bool
    {
        return $playDate->isTraining() && $playDate->getDate() >= $this->timeService->today();
    }

    private function getShowChangeRequestLink(PlayDate $playDate, Clown $currentClown): bool
    {
        if ($playDate->isTraining() || !$playDate->getPlayingClowns()->contains($currentClown)) {
            return false;
        }

        return $playDate->getDate() >= $this->timeService->today()->modify(PlayDateChangeRequestCloseInvalidService::CREATABLE_UNTIL_PERIOD);
    }
}
