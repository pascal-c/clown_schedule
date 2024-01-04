<?php

namespace App\ViewController;

use App\Entity\Month;
use App\Repository\ScheduleRepository;
use App\Value\ScheduleStatus;
use App\ViewModel\Schedule;

class ScheduleViewController
{
    public function __construct(private DayViewController $dayViewController, private ScheduleRepository $scheduleRepository)
    {
    }

    public function getSchedule(Month $month): Schedule
    {
        $status = $this->scheduleRepository->find($month)?->getStatus();
        $schedule = new Schedule($status ?? ScheduleStatus::NOT_STARTED, $month);

        $days = [];
        foreach ($month->days() as $date) {
            $days[$date->format('d')] = $this->dayViewController->getDay($date);
        }
        $schedule->setDays($days);

        return $schedule;
    }
}
