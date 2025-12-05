<?php

namespace App\Component;

use App\Service\AuthService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('schedule_navigation', template: 'components/sub_navigation.html.twig')]
class ScheduleNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'schedule';

    public function __construct(private UrlGeneratorInterface $urlHelper, private AuthService $authService)
    {
    }

    public function mount()
    {
        $currentClown = $this->authService->getCurrentClown();
        $isAdmin = $currentClown->isAdmin();
        $this->navigationItems = array_filter(
            [
                'schedule' => ['label' => 'Spielplan', 'url' => $this->urlHelper->generate('schedule')],
                'clown_invoice' => ['label' => 'Rechnungsansicht', 'url' => $this->urlHelper->generate('clown_invoice_show', ['clownId' => $currentClown->getId()])],
                'play_dates_by_year' => ['label' => 'Tabellarische Jahresansicht', 'url' => $this->urlHelper->generate('play_date_index'), 'hide' => !$isAdmin],
                'calendar_export' => ['label' => 'Kalender Export', 'url' => $this->urlHelper->generate('calendar_export'), 'badge' => 'neu'],
                // 'statistics' => ['label' => 'Statistiken', 'url' => $this->urlHelper->generate('statistics')],
            ],
            fn ($item) => empty($item['hide'])
        );
    }
}
