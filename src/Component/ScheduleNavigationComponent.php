<?php

namespace App\Component;

use App\Entity\Clown;
use App\Service\AuthService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('schedule_navigation')]
class ScheduleNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'schedule';
    public ?Clown $currentClown;

    public function __construct(private UrlGeneratorInterface $urlHelper, private AuthService $authService)
    {
    }

    public function mount()
    {
        $this->currentClown = $this->authService->getCurrentClown();
        $this->navigationItems = array_filter(
            [
                'schedule' => ['label' => 'Spielplan', 'url' => $this->urlHelper->generate('schedule')],
                'calculate' => ['label' => 'Spielplan erstellen', 'url' => $this->urlHelper->generate('calculate'), 'admin' => true],
                'clown_invoice' => ['label' => 'Rechnungsansicht', 'url' => $this->urlHelper->generate('clown_invoice_show', ['clownId' => $this->currentClown->getId()])],
                'play_dates_by_year' => ['label' => 'Tabellarische Jahresansicht', 'url' => $this->urlHelper->generate('play_date_index'), 'admin' => true],
                // 'statistics' => ['label' => 'Statistiken', 'url' => $this->urlHelper->generate('statistics')],
            ],
            fn ($item) => !isset($item['admin']) || $this->currentClown->isAdmin()
        );
    }
}
