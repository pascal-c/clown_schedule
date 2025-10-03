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

    public static function fromArray(array $data): RosterResult
    {
        return new RosterResult(
            success: $data['success'] ?? false,
            statusCode: $data['statusCode'] ?? 500,
            errorMessage: $data['errorMessage'] ?? '',
            assignments: $data['assignments'] ?? [],
            personalResults: $data['personalResults'] ?? [],
            rating: $data['rating'] ?? ['total' => -1],
            firstResultTotalPoints: $data['firstResultTotalPoints'] ?? 0,
            counter: $data['counter'] ?? 0,
            isTimedOut: $data['isTimedOut'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'statusCode' => $this->statusCode,
            'errorMessage' => $this->errorMessage,
            'assignments' => $this->assignments,
            'personalResults' => $this->personalResults,
            'rating' => $this->rating,
            'firstResultTotalPoints' => $this->firstResultTotalPoints,
            'counter' => $this->counter,
            'isTimedOut' => $this->isTimedOut,
        ];
    }
}
