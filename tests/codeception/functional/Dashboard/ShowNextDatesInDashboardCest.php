<?php

declare(strict_types=1);

namespace App\Tests\Functional\Dashboard;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Value\PlayDateType;
use App\Value\TimeSlotInterface;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class ShowNextDatesInDashboardCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $currentClown = $this->clownFactory->create(
            name: 'Hugo',
            email: 'hugo@example.org',
            password: 'secret',
        );
        $venue = $this->venueFactory->create(
            name: 'Seniorenheim Asselborn',
            daytimeDefault: TimeSlotPeriodInterface::ALL,
            meetingTime: '12:00',
            playTimeFrom: '13:00',
            playTimeTo: '16:00',
        );
        $this->playDateFactory->create(
            date: new DateTimeImmutable('2024-12-24'),
            venue: $venue,
            playingClowns: [$currentClown, $this->clownFactory->create(name: 'Marie')],
        );
        $this->playDateFactory->create(
            date: new DateTimeImmutable('2025-01-01'),
            daytime: TimeSlotInterface::AM,
            playingClowns: [$currentClown],
            type: PlayDateType::SPECIAL,
            title: 'Spezialtermin',
        );
        $this->playDateFactory->create(
            date: new DateTimeImmutable('2024-12-25'),
            venue: $this->venueFactory->create(name: 'Anderes Heim'),
            playingClowns: [$this->clownFactory->create(name: 'Anderer Clown')],
        );
        $this->playDateFactory->create(
            date: new DateTimeImmutable('2024-12-14'),
            daytime: TimeSlotInterface::AM,
            playingClowns: [$currentClown],
        );
    }

    public function showNextDates(FunctionalTester $I): void
    {
        Functional::$now = '2024-12-15';

        $I->login(email: 'hugo@example.org', password: 'secret');
        $I->amOnPage('/');

        $I->amGoingTo('test the values of the regular play date');
        $I->see('Deine nächsten Termine, Hugo', 'h4');
        $I->see('24.12.2024 ganztags', '//table/tbody/tr[1]');
        $I->seeLink('Seniorenheim Asselborn');

        $playDateRow = Locator::contains('table tr', text: '24.12.2024 ganztags');
        $I->see('Seniorenheim Asselborn', $playDateRow);
        $I->see('12:00', $playDateRow);
        $I->see('13:00 - 16:00', $playDateRow);
        $I->see('Marie', $playDateRow);

        $I->amGoingTo('check the values of the special play date');
        $I->see('01.01.2025 vormittags', '//table/tbody/tr[2]');
        $I->see('Spezialtermin', '//table/tbody/tr[2]');

        $I->amGoingTo('check that a play date not assigned to me is not shown');
        $I->dontSee('25.12.2025');
        $I->dontSee('Anderes Heim');
        $I->dontSee('Anderer Clown');

        $I->amGoingTo('check that a play date in the past is not shown');
        $I->dontSee('14.12.2024');
        $I->dontSee('Anderes Heim');
        $I->dontSee('Anderer Clown');
    }
}
