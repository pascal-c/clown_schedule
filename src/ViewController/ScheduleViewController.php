<?php

namespace App\ViewController;

use App\Entity\Month;
use App\Entity\Vacation;
use App\Repository\VacationRepository;
use App\ViewModel\Day;
use App\ViewModel\Schedule;
use IntlDateFormatter;

class ScheduleViewController
{
    public function __construct(private DayViewController $dayViewController)
    {
    }

    public function getSchedule(Month $month): Schedule
    {
        $schedule = new Schedule($month);

        $days = [];
        foreach($month->days() as $day) {
            $days[$day->format('d')] = $this->dayViewController->getDay($day);
        }
        $schedule->setDays($days);
        
        return $schedule;
    }
}
