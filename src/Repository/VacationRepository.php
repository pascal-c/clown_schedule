<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Month;
use App\Entity\Vacation;
use App\Gateway\VacationGateway;
use DateTimeImmutable;

class VacationRepository 
{
    public function __construct(private VacationGateway $holidayGateway)
    {
    }

    public function byMonth(Month $month): array
    {
        $byYear = $this->holidayGateway->findByYear($month->getYear());
        return array_map(
            fn(array $x) => new Vacation(
                new DateTimeImmutable($x['start']), 
                new DateTimeImmutable( $x['end']), 
                $x['name']  
            ),
            $byYear 
        );
    }
}
