<?php

namespace App\Component;

use App\Entity\Clown;
use App\Entity\Month;
use App\Service\AuthService;
use App\Service\TimeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('month_navigation', template: 'components/sub_navigation.html.twig')]
class MonthNavigationComponent
{
    public string $active;
    public ?string $urlKey;
    public array $urlParams = [];
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
        $this->urlParams = $urlParams;
        $this->active = $active;

        $activeMonth = Month::build($this->active);
        $navigationItems = [];
        $navigationItems['previousYear'] = [
            'label' => '<<',
            'url' => $this->urlForMonth($activeMonth->previousYear()),
            'li_class' => 'd-none d-lg-block',
            'title' => 'Vorheriges Jahr',
        ];
        $navigationItems['previousMonth'] = [
            'label' => '<',
            'url' => $this->urlForMonth($activeMonth->previous()),
            'title' => 'Vorheriger Monat',
        ];

        $currentMonth = new Month($this->timeService->today())->previous()->previous();
        for ($i = 1; $i <= 6; ++$i) {
            $navigationItems[$currentMonth->getKey()] = [
                'label' => $currentMonth->getLabel(),
                'url' => $this->urlForMonth($currentMonth),
                // 3 is current month and always visible, 4 and 5 are visible from sm breakpoint, 1,2,6 are only visible on lg breakpoint
                'li_class' => ($i > 3 && $i < 6) ? 'd-none d-sm-block' : (($i >= 6 || $i < 3) ? 'd-none d-lg-block' : ''),
            ];
            $currentMonth = $currentMonth->next();
        }

        $navigationItems['nextMonth'] = [
            'label' => '>',
            'url' => $this->urlForMonth($activeMonth->next()),
            'title' => 'Nächster Monat',
        ];
        $navigationItems['nextYear'] = [
            'label' => '>>',
            'url' => $this->urlForMonth($activeMonth->nextYear()),
            'li_class' => 'd-none d-lg-block',
            'title' => 'Nächstes Jahr',
        ];

        $this->navigationItems = $navigationItems;
    }

    private function urlForMonth(Month $month): string
    {
        return $this->urlHelper->generate(
            $this->urlKey,
            array_merge($this->urlParams, ['monthId' => $month->getKey()])
        );
    }
}
