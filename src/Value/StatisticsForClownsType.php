<?php

declare(strict_types=1);

namespace App\Value;

enum StatisticsForClownsType: string
{
    case SUPER = 'super';
    case WISHED_PLAYS_MONTH = 'wishedPlaysMonth';
    case TARGET_PLAYS = 'targetPlays';
    case CALCULATED_PLAYS_MONTH = 'calculatedPlaysMonth';
    case SCHEDULED_PLAYS_MONTH = 'scheduledPlaysMonth';

    public function label(): string
    {
        return match($this) {
            StatisticsForClownsType::SUPER => 'Gespielte Termine gesamt',
            StatisticsForClownsType::WISHED_PLAYS_MONTH => 'Gewünschte Termine',
            StatisticsForClownsType::TARGET_PLAYS => 'Zieltermine',
            StatisticsForClownsType::CALCULATED_PLAYS_MONTH => 'Berechnete Termine',
            StatisticsForClownsType::SCHEDULED_PLAYS_MONTH => 'Zugeteilte Termine',
        };
    }

    public function labelNumerator(): string
    {
        return match($this) {
            StatisticsForClownsType::SUPER => 'Super-Spieltermine',
            StatisticsForClownsType::WISHED_PLAYS_MONTH => 'Zugeteilte Termine',
            StatisticsForClownsType::TARGET_PLAYS => 'Zugeteilte Termine',
            StatisticsForClownsType::CALCULATED_PLAYS_MONTH => 'Zugeteilte Termine',
            StatisticsForClownsType::SCHEDULED_PLAYS_MONTH => 'Gespielte Termine gesamt',
        };
    }
}
