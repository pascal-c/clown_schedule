<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Month;
use App\Service\TimeService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MonthRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    public function find(SessionInterface $session, ?string $id): Month
    {
        if (is_null($id)) {
            $id = $session->get('month_id');
            if (is_null($id)) {
                $id = $this->timeService->now()->format('Y-m');
            }
        } else {
            $session->set('month_id', $id);
        }

        return Month::build($id);
    }
}
