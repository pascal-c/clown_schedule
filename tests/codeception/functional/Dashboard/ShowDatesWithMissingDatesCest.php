<?php

declare(strict_types=1);

namespace App\Tests\Functional\Dashboard;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use App\Value\PlayDateType;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;

class ShowDatesWithMissingDatesCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->configFactory->update(featureTeamsActive: true, teamCanAssignPlayingClowns: true);
        Functional::$now = '2024-12-15';

        $adminClown = $this->clownFactory->create(
            name: 'Hugo',
            email: 'admin@example.org',
            password: 'secret',
            isAdmin: true,
        );
        $teamClown = $this->clownFactory->create(
            name: 'Marie',
            email: 'marie@example.org',
            password: 'secret',
            isAdmin: false,
        );
        $noTeamClown = $this->clownFactory->create(
            name: 'Andie',
            email: 'andie@example.org',
            password: 'secret',
            isAdmin: false,
        );
        $venue = $this->venueFactory->create(
            name: 'Seniorenheim Asselborn',
            daytimeDefault: TimeSlotPeriodInterface::ALL,
            meetingTime: '12:00',
            playTimeFrom: '13:00',
            playTimeTo: '16:00',
            teamActive: true,
            team: [$teamClown],
        );
        $this->playDateFactory->create( // never shown - already 2 clowns
            date: new DateTimeImmutable('2024-12-16'),
            venue: $venue,
            playingClowns: [$noTeamClown, $teamClown],
        );
        $this->playDateFactory->create( // shown for admin and team clown
            date: new DateTimeImmutable('2024-12-17'),
            venue: $venue,
            playingClowns: [$noTeamClown],
        );
        $this->playDateFactory->create( // only shown for admin, there is no team
            date: new DateTimeImmutable('2024-12-18'),
            daytime: TimeSlotInterface::AM,
            playingClowns: [$adminClown],
            type: PlayDateType::SPECIAL,
            title: 'Spezialtermin',
        );
        $this->playDateFactory->create( // only shown for admin, there is no team
            date: new DateTimeImmutable('2024-12-19'),
            venue: $this->venueFactory->create(name: 'Anderes Heim'),
        );
    }

    public function showMissingDatesForAdmin(AdminTester $I): void
    {
        $I->login(email: 'admin@example.org', password: 'secret');
        $I->amOnPage('/');

        $I->see('Termine mit fehlender Besetzung', 'h4');
        $I->see('17.12.2024', '//table/tbody/tr[1]');
        $I->see('18.12.2024', '//table/tbody/tr[2]');
        $I->see('19.12.2024', '//table/tbody/tr[3]');

        $I->see('ACHTUNG! Die Spielplanberechnung für den Monat Jan. 2025 wurde noch nicht abgeschlossen.');
    }

    public function showMissingDatesForNoTeamClown(FunctionalTester $I): void
    {
        $I->login(email: 'andie@example.org', password: 'secret');
        $I->amOnPage('/');

        $I->dontSee('Termine mit fehlender Besetzung', 'h4');
        $I->dontSee('17.12.2024', '//table/tbody/tr[1]');
        $I->dontSee('18.12.2024', '//table/tbody/tr[2]');
        $I->dontSee('19.12.2024', '//table/tbody/tr[3]');

        $I->dontSee('ACHTUNG! Die Spielplanberechnung für den Monat Jan. 2025 wurde noch nicht abgeschlossen.');
    }

    public function showMissingDatesForTeamClown(FunctionalTester $I): void
    {
        $I->login(email: 'marie@example.org', password: 'secret');
        $I->amOnPage('/');

        $I->see('Termine mit fehlender Besetzung', 'h4');
        $I->see('17.12.2024', '//table/tbody/tr[1]');
        $I->dontSee('18.12.2024');
        $I->dontSee('19.12.2024');

        $I->dontSee('ACHTUNG! Die Spielplanberechnung für den Monat Jan. 2025 wurde noch nicht abgeschlossen.');
    }
}
