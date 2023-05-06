<?php
namespace App\Component;

use App\Entity\Clown;
use App\Entity\Substitution;
use App\Repository\SubstitutionRepository;
use App\Service\AuthService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('show_substitution_clown')]
final class ShowSubstitutionClownComponent
{
    public ?Substitution $substitution;
    public ?Clown $currentClown;

    public function __construct(private SubstitutionRepository $substitutionRepository, private AuthService $authService) {}

    public function mount(\DateTimeImmutable $date, string $daytime) {
        $this->substitution = $this->substitutionRepository->find($date, $daytime);
        if (is_null($this->substitution)) {
            $this->substitution = new Substitution;
            $this->substitution->setDate($date)->setDaytime($daytime);
        }

        $this->currentClown = $this->authService->getCurrentClown();
    }

}
