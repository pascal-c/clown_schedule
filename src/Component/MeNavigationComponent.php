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
        $navigationItems = [];

        foreach (MeSubnavigationElement::cases() as $meSubnavigationElement) {
            $navigationItems[$meSubnavigationElement->value] = [
                'label' => $meSubnavigationElement->label(),
                'url' => $this->urlHelper->generate(
                    'me',
                    ['subnavigationElement' => $meSubnavigationElement->value],
                ),
            ];
        }

        $this->navigationItems = $navigationItems;
    }
}
