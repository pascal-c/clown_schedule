<?php

namespace App\Service\Scheduler;

class ResultComparator
{
    public function __construct(private ResultRater $resultRater)
    {
    }

    public function isDefinitelyWorseThan(Result $result1, Result $result2): bool
    {
        $rate1 = ($this->resultRater)($result1, ignoreTargetPlays: true);
        $rate2 = ($this->resultRater)($result2);

        return $rate1 > $rate2;
    }

    public function isWorseThan(Result $result1, Result $result2): bool
    {
        $rate1 = ($this->resultRater)($result1);
        $rate2 = ($this->resultRater)($result2);

        return $rate1 > $rate2;
    }
}
