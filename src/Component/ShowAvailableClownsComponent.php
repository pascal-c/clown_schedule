<?php
namespace App\Component;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Repository\ClownAvailabilityRepository;
use App\Service\Scheduler\AvailabilityChecker;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('show_available_clowns')]
final class ShowAvailableClownsComponent
{
    public array $entries;

    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private AvailabilityChecker $availabilityChecker
    ) {}

    public function mount(\DateTimeImmutable $date, string $daytime) {
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth(new Month($date));
        $availableClownAvailabilities = array_filter(
            $clownAvailabilities,
            fn(ClownAvailability $availability) => $this->availabilityChecker->isAvailableOn($date, $daytime, $availability)
        );
        $this->entries = array_map(
            fn(ClownAvailability $availability) => [
                'clown' => $availability->getClown(),
                'type' => $this->getType($date, $daytime, $availability),
                'messages' => $this->getMessages($date, $daytime, $availability),
            ],
            $availableClownAvailabilities
        );

        usort(
            $this->entries, 
            function(array $entry1, array $entry2)
            {
                $mapping = ['success' => 0, 'warning' => 1, 'danger' => 2];
                return $mapping[$entry1['type']] <=> $mapping[$entry2['type']];
            }
        );
    }

    private function getType(\DateTimeImmutable $date, string $daytime, ClownAvailability $clownAvailability): string
    {
        if ($this->availabilityChecker->maxPlaysMonthReached($clownAvailability) ||
            $this->availabilityChecker->maxPlaysDayReached($date, $clownAvailability)) {
            return 'danger';   
        } elseif ($clownAvailability->getAvailabilityOn($date, $daytime) == 'maybe')
        {
            return 'warning';
        }

        return 'success';
    }

    private function getMessages(\DateTimeImmutable $date, string $daytime, ClownAvailability $clownAvailability): array
    {
        $messages = [];
        if ($clownAvailability->getAvailabilityOn($date, $daytime) == 'maybe') {
            $messages[] = 'Clown kann nur wenn\'s sein muss.'; 
        }
        if ($this->availabilityChecker->maxPlaysMonthReached($clownAvailability)) {
            $messages[] = 'Maximale Anzahl monatlicher Spiele erreicht!';
        }
        if ($this->availabilityChecker->maxPlaysDayReached($date, $clownAvailability)) {
            $messages[] = 'Maximale Anzahl täglicher Spiele erreicht!';
        }
        if (empty($messages)) {
            $messages[] = 'Clown ist verfügbar!';
        }

        return $messages;
    }
}
