<?php
namespace App\Component;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('main_navigation')]
class MainNavigationComponent
{
    public array $navigationItems = [];
    public string $active = 'clown';

    public function __construct(private UrlGeneratorInterface $urlHelper)
    {
    }

    public function mount() {
        $this->navigationItems = [
            'clown' => ['label' => 'Clowns', 'url' => $this->urlHelper->generate('clown_index')],
            'availability' => ['label' => 'Fehlzeiten', 'url' => $this->urlHelper->generate('clown_availability_index')],
            'venue' => ['label' => 'Spielorte', 'url' => $this->urlHelper->generate('venue_index')],
            'play_date' => ['label' => 'Spielplan', 'url' => $this->urlHelper->generate('schedule')],
        ];
    }
}
