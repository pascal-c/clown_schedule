<?php

declare(strict_types=1);

namespace App\Service\CalendarExporter;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Repository\SubstitutionRepository;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Occurrence;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateConverter
{
    public function __construct(
        private TranslatorInterface $translator,
        private SubstitutionRepository $substitutionRepository,
    ) {
    }

    public function getName(PlayDate $date): string
    {
        $status = match($date->getStatus()) {
            PlayDate::STATUS_CANCELLED => ' ABGESAGT',
            PlayDate::STATUS_MOVED => ' VERSCHOBEN',
            default => '',
        };

        return $date->getName().$status;
    }

    public function getDescription(PlayDate $date): string
    {
        $substitutionClowns = array_filter(array_map(
            fn (Substitution $substitution): ?Clown => $substitution->getSubstitutionClown(),
            $this->substitutionRepository->findByTimeSlotPeriod($date),
        ));
        $substitutions = implode(
            ', ',
            array_map(fn (Clown $clown): string => $clown->getName(), $substitutionClowns)
        );
        $playingClowns = implode(
            ', ',
            $date->getPlayingClowns()->map(fn (Clown $clown): string => $clown->getName())->toArray()
        );

        return
            "Es spielen: $playingClowns\n".
            "Springer: $substitutions\n".
            "Tageszeit: {$this->translator->trans($date->getDaytime())}\n".
            "Treffen: {$date->getMeetingTime()?->format('H:i')}\n".
            "Spielzeit: {$date->getPlayTimeFrom()?->format('H:i')}-{$date->getPLayTimeTo()?->format('H:i')}\n".
            ($date->getComment() ? "Kommentar: {$date->getComment()}" : '')
        ;

    }

    public function getOccurence(PlayDate $date): Occurrence
    {
        if (is_null($date->getPlayTimeFrom()) || is_null($date->getPlayTimeTo())) {
            return new SingleDay(new Date($date->getDate()));
        }

        $startDate = $date->getDate()->setTime(
            (int) $date->getPlayTimeFrom()->format('H'),
            (int) $date->getPlayTimeFrom()->format('i')
        );
        $endDate = $date->getDate()->setTime(
            (int) $date->getPlayTimeTo()->format('H'),
            (int) $date->getPlayTimeTo()->format('i')
        );
        $start = new DateTime($startDate, false);
        $end = new DateTime($endDate, false);

        return new TimeSpan($start, $end);
    }

    public function getLocation(PlayDate $date): ?Location
    {
        if (is_null($date->getVenue())) {
            return null;
        }

        return new Location($date->getVenue()->getName());
    }
}
