<?php

namespace App\ViewController;

use App\Entity\Month;
use App\ViewModel\Schedule;

class ScheduleViewController
{
    public function __construct(private DayViewController $dayViewController)
    {
    }

    public function getSchedule(Month $month): Schedule
    {
        $schedule = new Schedule($month);

        $days = [];
        foreach($month->days() as $date) {
            $days[$date->format('d')] = $this->dayViewController->getDay($date);
        }
        $schedule->setDays($days);
        
        return $schedule;
    }
}
