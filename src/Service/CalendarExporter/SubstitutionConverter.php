<?php

declare(strict_types=1);

namespace App\Service\CalendarExporter;

use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Repository\PlayDateRepository;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\Occurrence;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubstitutionConverter
{
    public function __construct(
        private TranslatorInterface $translator,
        private PlayDateRepository $playDateRepository,
    ) {
    }

    public function getSummary(Substitution $substitution): string
    {
        return "Springer {$this->translator->trans($substitution->getDaytime())}";
    }

    public function getDescription(Substitution $substitution): string
    {
        $playDates = $this->playDateRepository->findConfirmedByTimeSlotPeriod($substitution);
        $playDatesString = implode(
            "\n",
            array_map(
                fn (PlayDate $playDate): string => $playDate->getName().' - '.$this->translator->trans($playDate->getDaytime()),
                $playDates,
            )
        );

        return
            "Springer für:\n$playDatesString";

    }

    public function getOccurence(Substitution $substitution): Occurrence
    {
        return new SingleDay(new Date($substitution->getDate()));
    }
}
