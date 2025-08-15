<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\RecurringDate;
use App\Entity\Venue;
use App\Lib\Collection;
use App\Service\TimeService;
use DateTimeImmutable;
use Symfony\Contracts\Service\Attribute\Required;

class RecurringDateFactory extends AbstractFactory
{
    protected VenueFactory $venueFactory;
    protected TimeService $timeService;

    #[Required]
    public function _inject(VenueFactory $venueFactory)
    {
        $this->venueFactory = $venueFactory;
        $this->timeService = new TimeService();
    }

    public function create(
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
        ?Venue $venue = null,
        string $rhythm = RecurringDate::RHYTHM_WEEKLY,
        string $dayOfWeek = 'Tuesday',
        int $every = 2,
        ?string $daytime = null,
        ?string $meetingTime = null,
        ?string $playTimeFrom = null,
        ?string $playTimeTo = null,
        bool $isSuper = false,
        array $playDates = [],
    ): RecurringDate {
        $startDate ??= $this->timeService->today();
        $endDate ??= $this->timeService->endOfYear();
        $venue ??= $this->venueFactory->create();
        list($daytimeGenerated, $meetingTimeGenerated, $playTimeFromGenerated, $playTimeToGenerated) = $this->timeOptions()->sample();
        $recurringDate = (new RecurringDate())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setVenue($venue)
            ->setRhythm($rhythm)
            ->setDayOfWeek($dayOfWeek)
            ->setEvery($every ?? 2)
            ->setDaytime($daytime ?? $daytimeGenerated)
            ->setMeetingTime(new DateTimeImmutable($meetingTime ?? $meetingTimeGenerated))
            ->setPlayTimeFrom(new DateTimeImmutable($playTimeFrom ?? $playTimeFromGenerated))
            ->setPlayTimeTo(new DateTimeImmutable($playTimeTo ?? $playTimeToGenerated))
            ->setIsSuper($isSuper)
        ;
        foreach ($playDates as $playDate) {
            $recurringDate->addPlayDate($playDate);
        }
        $this->entityManager->persist($recurringDate);
        $this->entityManager->flush();

        return $recurringDate;
    }

    private function timeOptions(): Collection
    {
        return new Collection([
            ['am', '08:30', '09:00', '11:00'],
            ['am', '09:00', '09:30', '12:00'],
            ['am', '09:30', '10:00', '12:00'],
            ['pm', '14:30', '15:00', '17:00'],
            ['pm', '15:00', '15:30', '18:00'],
            ['pm', '15:15', '16:00', '18:00'],
            ['all', '11:00', '12:30', '16:00'],
        ]);
    }
}
