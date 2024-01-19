<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Service\AuthService;
use App\Service\TimeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('month_navigation')]
class MonthNavigationComponent
{
    public string $active;
    public ?string $urlKey;
    public array $navigationItems;
    public Clown $currentClown;

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private TimeService $timeService,
        private AuthService $authService,
    ) {
    }

    public function mount(string $urlKey, array $urlParams = [], string $active = 'now'): void
    {
        $this->currentClown = $this->authService->getCurrentClown();
        $this->urlKey = $urlKey;
        $this->active = $active;

        $currentMonth = new Month($this->timeService->today());
        $activeMonth = Month::build($this->active);
        $navigationItems = [];
        $navigationItems['previous'] = [
            'label' => '<<',
            'url' => $this->urlHelper->generate(
                $this->urlKey,
                array_merge($urlParams, ['monthId' => $activeMonth->previous()->getKey()])
            ),
        ];
        for ($i = 1; $i <= 3; ++$i) {
            $navigationItems[$currentMonth->getKey()] = [
                'label' => $currentMonth->getLabel(),
                'url' => $this->urlHelper->generate(
                    $this->urlKey,
                    array_merge($urlParams, ['monthId' => $currentMonth->getKey()])
                ),
            ];
            $currentMonth = $currentMonth->next();
        }
        $navigationItems['next'] = [
            'label' => '>>',
            'url' => $this->urlHelper->generate(
                $this->urlKey,
                array_merge($urlParams, ['monthId' => $activeMonth->next()->getKey()])
            ),
        ];

        $this->navigationItems = $navigationItems;
    }
}
