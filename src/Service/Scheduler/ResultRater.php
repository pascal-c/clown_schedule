<?php

namespace App\Service\Scheduler;

class ResultRater
{
    public function __construct(private Rater $rater)
    {
    }

    public function currentPoints(Result $result, int $realPlayDateCount): int
    {
        $resultPlayDateCount = count($result);

        return $this->rater->totalPoints($result->getMonth(), ignoreTargetPlays: $resultPlayDateCount < $realPlayDateCount)
            - ($realPlayDateCount - $resultPlayDateCount) * Rater::POINTS_PER_MISSING_CLOWN;
    }
}
