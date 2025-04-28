<?php

declare(strict_types=1);

namespace App\Gateway\RosterCalculator;

class RosterResult
{
    public function __construct(
        public readonly bool $success = true,
        public readonly int $statusCode = 201,
        public readonly string $errorMessage = '',
        public readonly array $assignments = [],
        public readonly array $personalResults = [],
        public readonly array $rating = ['total' => -1],
        public readonly int $firstResultTotalPoints = 0,
        public readonly int $counter = 0,
        public readonly bool $isTimedOut = false,
    ) {
    }
}
