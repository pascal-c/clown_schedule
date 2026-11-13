<?php

namespace App\Component;

use App\Service\AuthService;
use App\Service\ConfigService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('schedule_navigation', template: 'components/sub_navigation.html.twig')]
class ScheduleNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'schedule';
    public bool $navbarToggler = true;

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private AuthService $authService,
        private ConfigService $configService,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount()
    {
        $currentClown = $this->authService->getCurrentClown();
        $this->navigationItems = array_filter(
            [
                'schedule' => ['label' => $this->translator->trans('Spielplan'), 'url' => $this->urlHelper->generate('schedule')],
                'clown_invoice' => [
                    'label' => $this->translator->trans('Rechnungsansicht'),
                    'url' => $this->urlHelper->generate('clown_invoice_show', ['clownId' => $currentClown->getId()]),
                    'show' => $this->configService->hasFeatureInvoiceManagementClowns(),
                ],
                'play_dates_by_year' => [
                    'label' => $this->translator->trans('Tabellarische Jahresansicht'),
                    'url' => $this->urlHelper->generate('play_date_index'),
                    'show' => $currentClown->isAdmin(),
                ],
                'calendar_export' => ['label' => $this->translator->trans('Kalender abonnieren'), 'url' => $this->urlHelper->generate('calendar_export')],
            ],
            fn ($item) => !array_key_exists('show', $item) || $item['show']
        );
    }
}
