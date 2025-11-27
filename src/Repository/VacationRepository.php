<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vacation;
use App\Gateway\VacationGateway;
use DateTimeImmutable;

class VacationRepository
{
    public function __construct(private VacationGateway $vacationGateway, private ConfigRepository $configRepository)
    {
    }

    public function byYear(string $year): array
    {
        $federalState = $this->configRepository->find()->getFederalState();
        $thisYear = $this->vacationGateway->findByYear($federalState, $year);
        $lastYear = $this->vacationGateway->findByYear($federalState, strval($year - 1));

        return array_map(
            fn (array $x) => new Vacation(
                new DateTimeImmutable($x['start']),
                new DateTimeImmutable($x['end']),
                $x['name']
            ),
            array_merge($lastYear, $thisYear)
        );
    }
}
