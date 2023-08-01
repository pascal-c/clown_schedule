<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Month;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MonthRepository 
{
    public function find(SessionInterface $session, ?string $id): Month
    {
        if (is_null(($id))) {
            $id = $session->get('month_id', 'now');
        }
        else {
            $session->set('month_id', $id);
        }

        return Month::build($id);
    }
}
