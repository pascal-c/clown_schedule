<?php

namespace App\Component;

use App\Value\MeSubnavigationElement;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('me_navigation', template: 'components/sub_navigation.html.twig')]
class MeNavigationComponent
{
    public string $active;
    public array $navigationItems;

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
    ) {
    }

    public function mount(string $active = 'me'): void
    {
        $this->active = $active;

        $navigationItems[MeSubnavigationElement::PERSONAL_DATA->value] = [
            'label' => 'Persönliche Daten',
            'url' => $this->urlHelper->generate(
                'me',
                ['subnavigationElement' => MeSubnavigationElement::PERSONAL_DATA->value],
            ),
        ];
        $navigationItems[MeSubnavigationElement::NOTIFICATIONS->value] = [
            'label' => 'Benachrichtigungen',
            'url' => $this->urlHelper->generate(
                'me',
                ['subnavigationElement' => MeSubnavigationElement::NOTIFICATIONS->value],
            ),
        ];

        $this->navigationItems = $navigationItems;
    }
}
