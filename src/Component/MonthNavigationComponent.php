<?php
namespace App\Component;

use App\Entity\Month;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent('month_navigation')]
class MonthNavigationComponent
{
    public ?string $active;
    public ?string $urlKey;
    public array $urlParams = [];

    public function __construct(private UrlGeneratorInterface $urlHelper) {}

    #[ExposeInTemplate()]
    public function getNavigationItems(): array
    {
        $currentMonth = new Month(new \DateTimeImmutable('-2 month'));
        $navigationItems = [];
        for ($i = 1; $i <= 5; $i++) {
            $navigationItems[$currentMonth->getKey()] = [
                'label' => $currentMonth->getLabel(), 
                'url' => $this->urlHelper->generate(
                    $this->urlKey,
                    array_merge($this->urlParams, ['monthId' => $currentMonth->getKey()])
                ),
            ];
            $currentMonth = $currentMonth->next();
        }
        return $navigationItems;
    }
}
