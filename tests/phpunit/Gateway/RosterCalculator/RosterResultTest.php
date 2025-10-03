<?php

declare(strict_types=1);

namespace App\Tests\Gateway\RosterCalculator;

use App\Gateway\RosterCalculator\RosterResult;
use PHPUnit\Framework\TestCase;

class RosterResultTest extends TestCase
{
    public function testFromArray(): void
    {
        $rosterResultArray = [
            'success' => true,
            'statusCode' => 200,
            'errorMessage' => '',
            'assignments' => ['assignments'],
            'personalResults' =>  ['personalResults'],
            'rating' => ['total' => 85],
            'firstResultTotalPoints' => 80,
            'counter' => 1500,
            'isTimedOut' => true,
        ];
        $expectedRosterResult = new RosterResult(
            success: true,
            statusCode: 200,
            errorMessage: '',
            assignments: ['assignments'],
            personalResults: ['personalResults'],
            rating: ['total' => 85],
            firstResultTotalPoints: 80,
            counter: 1500,
            isTimedOut: true,
        );
        $rosterResult = RosterResult::fromArray($rosterResultArray);

        $this->assertEquals($expectedRosterResult, $rosterResult);
    }

    public function testFromArrayWithIncompleteData(): void
    {
        $rosterResultArray = [
            'success' => false,
            'statusCode' => 500,
            'errorMessage' => 'errorMessage',
        ];
        $expectedRosterResult = new RosterResult(
            success: false,
            statusCode: 500,
            errorMessage: 'errorMessage',
            assignments: [],
            personalResults: [],
            rating: ['total' => -1],
            firstResultTotalPoints: 0,
            counter: 0,
            isTimedOut: false,
        );
        $rosterResult = RosterResult::fromArray($rosterResultArray);

        $this->assertEquals($expectedRosterResult, $rosterResult);
    }

    public function testToArray(): void
    {
        $rosterResultArray = [
            'success' => false,
            'statusCode' => 500,
            'errorMessage' => 'errorMessage',
        ];
        $expectedRosterResultArray = [
            'success' => false,
            'statusCode' => 500,
            'errorMessage' => 'errorMessage',
            'assignments' => [],
            'personalResults' => [],
            'rating' => ['total' => -1],
            'firstResultTotalPoints' => 0,
            'counter' => 0,
            'isTimedOut' => false,
        ];

        $rosterResult = RosterResult::fromArray($rosterResultArray);
        $returnedArray = $rosterResult->toArray();

        $this->assertSame($expectedRosterResultArray, $returnedArray);
    }

    public function testToArrayWithIncompleteData(): void
    {
        $rosterResultArray = [
            'success' => true,
            'statusCode' => 200,
            'errorMessage' => '',
            'assignments' => ['assignments'],
            'personalResults' =>  ['personalResults'],
            'rating' => ['total' => 85],
            'firstResultTotalPoints' => 80,
            'counter' => 1500,
            'isTimedOut' => true,
        ];

        $rosterResult = RosterResult::fromArray($rosterResultArray);
        $returnedArray = $rosterResult->toArray();

        $this->assertEquals($rosterResultArray, $returnedArray);
    }
}
