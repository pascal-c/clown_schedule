<?php

namespace App\Component\Venue;

use App\Entity\Venue;
use App\Service\ConfigService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('venue/details_navigation', template: 'components/sub_navigation.html.twig')]
class DetailsNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'details';

    public function __construct(
        private ConfigService $configService,
        private UrlGeneratorInterface $urlHelper,
        private TranslatorInterface $translator,
    ) {
    }

    public function mount(Venue $venue)
    {
        $this->navigationItems = array_filter(
            [
                'details' => ['label' => $this->translator->trans('Details'), 'url' => $this->urlHelper->generate('venue_show', ['id' => $venue->getId()])],
                'contacts' => ['label' => $this->translator->trans('Kontakte'), 'url' => $this->urlHelper->generate('venue_contact_index', ['id' => $venue->getId()])],
                'fees' => [
                    'label' => $this->translator->trans('Honorare'),
                    'url' => $this->urlHelper->generate('venue_fee_index', ['id' => $venue->getId()]),
                    'show' => $this->configService->hasFeatureInvoiceManagementClowns(),
                ],
                'play_dates' => ['label' => $this->translator->trans('Spieltermine'), 'url' => $this->urlHelper->generate('venue_play_date_index', ['id' => $venue->getId()])],
            ],
            fn ($item) => !array_key_exists('show', $item) || $item['show']
        );
    }
}
