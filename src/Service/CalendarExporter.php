<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Service\CalendarExporter\PlayDateConverter;
use App\Service\CalendarExporter\SubstitutionConverter;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class CalendarExporter
{
    public function __construct(
        private PlayDateConverter $playDateConverter,
        private SubstitutionConverter $substitutionConverter,
    ) {
    }

    /**
     * @param array<PlayDate>     $dates
     * @param array<Substitution> $substitutions
     */
    public function ics(array $dates, array $substitutions): string
    {
        $calendar = new Calendar();

        foreach ($dates as $date) {
            $event = new Event(new UniqueIdentifier('clown-spielplan-play-date'.$date->getId()));
            $event
                ->setSummary($this->playDateConverter->getName($date))
                ->setDescription($this->playDateConverter->getDescription($date))
                ->setOccurrence($this->playDateConverter->getOccurence($date))
                ->setLocation($this->playDateConverter->getLocation($date))
                ->setStatus($date->isConfirmed() ? EventStatus::CONFIRMED() : EventStatus::CANCELLED())
            ;
            $calendar->addEvent($event);
        }
        foreach ($substitutions as $substitution) {
            $event = new Event(new UniqueIdentifier('clown-spielplan-substitution'.$substitution->getId()));
            $event
                ->setSummary($this->substitutionConverter->getSummary($substitution))
                ->setDescription($this->substitutionConverter->getDescription($substitution))
                ->setOccurrence($this->substitutionConverter->getOccurence($substitution))
            ;
            $calendar->addEvent($event);
        }

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        return strval($calendarComponent);
    }
}
