<?php

namespace App\Component;

use App\Entity\Month;
use App\Service\TimeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent('month_navigation')]
class MonthNavigationComponent
{
    public string $active = 'now';
    public ?string $urlKey;
    public array $urlParams = [];

    public function __construct(
        private UrlGeneratorInterface $urlHelper,
        private TimeService $timeService
    ) {
    }

    #[ExposeInTemplate()]
    public function getNavigationItems(): array
    {
        $currentMonth = new Month($this->timeService->today());
        $activeMonth = Month::build($this->active);
        $navigationItems = [];
        $navigationItems['previous'] = [
            'label' => '<<',
            'url' => $this->urlHelper->generate(
                $this->urlKey,
                array_merge($this->urlParams, ['monthId' => $activeMonth->previous()->getKey()])
            ),
        ];
        for ($i = 1; $i <= 3; ++$i) {
            $navigationItems[$currentMonth->getKey()] = [
                'label' => $currentMonth->getLabel(),
                'url' => $this->urlHelper->generate(
                    $this->urlKey,
                    array_merge($this->urlParams, ['monthId' => $currentMonth->getKey()])
                ),
            ];
            $currentMonth = $currentMonth->next();
        }
        $navigationItems['next'] = [
            'label' => '>>',
            'url' => $this->urlHelper->generate(
                $this->urlKey,
                array_merge($this->urlParams, ['monthId' => $activeMonth->next()->getKey()])
            ),
        ];

        return $navigationItems;
    }
}
