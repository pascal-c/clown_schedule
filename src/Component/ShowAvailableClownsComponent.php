<?php

namespace App\Component;

use App\Entity\ClownAvailability;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Repository\ClownAvailabilityRepository;
use App\Service\Scheduler\AvailabilityChecker;
use App\Value\TimeSlotPeriod;
use App\Value\TimeSlotPeriodInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use DateTimeImmutable;

#[AsTwigComponent('show_available_clowns')]
final class ShowAvailableClownsComponent
{
    public array $entries;

    public function __construct(
        private ClownAvailabilityRepository $clownAvailabilityRepository,
        private AvailabilityChecker $availabilityChecker
    ) {
    }

    public function mount(DateTimeImmutable $date, string $daytime, ?PlayDate $playDate = null)
    {
        $timeSlotPeriod = new TimeSlotPeriod($date, $daytime);
        $clownAvailabilities = $this->clownAvailabilityRepository->byMonth(new Month($date));
        $availableClownAvailabilities = array_filter(
            $clownAvailabilities,
            fn (ClownAvailability $availability) => $this->availabilityChecker->isAvailableOn($timeSlotPeriod, $availability)
        );
        $this->entries = array_map(
            fn (ClownAvailability $availability) => [
                'clown' => $availability->getClown(),
                'type' => $this->getType($timeSlotPeriod, $availability, $playDate),
                'messages' => $this->getMessages($timeSlotPeriod, $availability, $playDate),
            ],
            $availableClownAvailabilities
        );

        usort(
            $this->entries,
            function (array $entry1, array $entry2) {
                $mapping = ['success' => 0, 'warning' => 1, 'danger' => 2];

                return $mapping[$entry1['type']] <=> $mapping[$entry2['type']];
            }
        );
    }

    private function getType(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability, ?PlayDate $playDate): string
    {
        if ($this->availabilityChecker->maxPlaysMonthReached($clownAvailability)
            || $this->availabilityChecker->maxPlaysDayReached($timeSlotPeriod->getDate(), $clownAvailability)
            || (!is_null($playDate) && $this->availabilityChecker->isBlocked($playDate->getVenue(), $clownAvailability->getClown()))) {
            return 'danger';
        } elseif ('maybe' == $clownAvailability->getAvailabilityOn($timeSlotPeriod)) {
            return 'warning';
        }

        return 'success';
    }

    private function getMessages(TimeSlotPeriodInterface $timeSlotPeriod, ClownAvailability $clownAvailability, ?PlayDate $playDate): array
    {
        $messages = [];
        if ('maybe' == $clownAvailability->getAvailabilityOn($timeSlotPeriod)) {
            $messages[] = 'Clown kann nur wenn\'s sein muss.';
        }
        if ($this->availabilityChecker->maxPlaysMonthReached($clownAvailability)) {
            $messages[] = 'Maximale Anzahl monatlicher Spiele erreicht!';
        }
        if ($this->availabilityChecker->maxPlaysDayReached($timeSlotPeriod->getDate(), $clownAvailability)) {
            $messages[] = 'Maximale Anzahl täglicher Spiele erreicht!';
        }
        if (!is_null($playDate) && $this->availabilityChecker->isBlocked($playDate->getVenue(), $clownAvailability->getClown())) {
            $messages[] = 'Clown ist für diesen Spielort gesperrt!';
        }
        if (empty($messages)) {
            $messages[] = 'Clown ist verfügbar!';
        }

        return $messages;
    }
}
