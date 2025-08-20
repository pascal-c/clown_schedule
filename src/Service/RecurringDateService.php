<?php

namespace App\Service;

use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\RecurringDate;
use App\Value\PlayDateType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class RecurringDateService
{
    public function __construct(
        private TimeService $timeService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function buildPlayDates(RecurringDate $recurringDate): void
    {
        if ($recurringDate->isWeekly()) {
            $this->buildWeeklyPlayDates($recurringDate);
        } else {
            $this->buildMonthlyPlayDates($recurringDate);
        }
    }

    private function buildWeeklyPlayDates(RecurringDate $recurringDate): void
    {
        $date = $recurringDate->getStartDate()->modify($recurringDate->getDayOfWeek());
        while ($date <= $recurringDate->getEndDate()) {
            $playDate = $this->buildPlayDate($recurringDate, $date);
            $recurringDate->addPlayDate($playDate);
            $date = $date->modify('+'.$recurringDate->getEvery().' weeks');
        }
    }

    private function buildMonthlyPlayDates(RecurringDate $recurringDate): void
    {
        $every = $recurringDate->getEvery();
        $dayOfWeek = $recurringDate->getDayOfWeek();
        $month = new Month($recurringDate->getStartDate());
        $date = $this->timeService->nThWeekdayOfMonth($every, $dayOfWeek, $month);

        while ($date <= $recurringDate->getEndDate()) {
            if ($date && $date >= $recurringDate->getStartDate()) {
                $playDate = $this->buildPlayDate($recurringDate, $date);
                $recurringDate->addPlayDate($playDate);
            }

            $month = $month->next();
            $date = $this->timeService->nThWeekdayOfMonth($every, $dayOfWeek, $month);
        }
    }

    private function buildPlayDate(RecurringDate $recurringDate, DateTimeImmutable $date): PlayDate
    {
        $playDate = new PlayDate();
        $playDate->setDate($date);
        $playDate->setDaytime($recurringDate->getDaytime());
        $playDate->setMeetingTime($recurringDate->getMeetingTime());
        $playDate->setPlayTimeFrom($recurringDate->getPlayTimeFrom());
        $playDate->setPlayTimeTo($recurringDate->getPlayTimeTo());
        $playDate->setVenue($recurringDate->getVenue());
        $playDate->setIsSuper($recurringDate->isSuper());
        $playDate->setType(PlayDateType::REGULAR);

        return $playDate;
    }

    public function deletePlayDatesSince(RecurringDate $recurringDate, DateTimeImmutable $date): int
    {
        $result = $this->entityManager
            ->createQuery('DELETE FROM App\Entity\PlayDate PD WHERE PD.recurringDate = :recurringDate AND PD.date >= :date')
            ->setParameter('recurringDate', $recurringDate)
            ->setParameter('date', $date)
            ->execute();

        $this->entityManager->refresh($recurringDate);
        if ($recurringDate->getEndDate() >= $date) {
            $recurringDate->setEndDate($date->modify('-1 day'));
        }
        if ($recurringDate->getPlayDates()->isEmpty()) {
            $this->entityManager->remove($recurringDate);
        }

        $this->entityManager->flush();

        return $result;
    }
}
