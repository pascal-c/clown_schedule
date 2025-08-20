<?php

namespace App\Tests\Functional\PlayDate;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class ShowRegularPlayDateCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2036-01-15');
        $daytime = TimeSlotPeriodInterface::PM;

        $venue = $this->venueFactory->create(
            name: 'Seniorenheim am See',
            meetingTime: '14:45',
            playTimeFrom: '15:30',
            playTimeTo: '17:30',
        );
        $this->playDate = $this->playDateFactory->create(
            venue: $venue,
            date: $date,
            daytime: $daytime,
            playingClowns: [$this->clownFactory->create(name: 'Hannah Hosianna'), $this->clownFactory->create(name: 'Uwe Popuwe')]
        );

        $this->substitutionFactory->create(date: $date, daytime: $daytime, clown: $this->clownFactory->create(name: 'Maria Popia'));
    }

    public function show(FunctionalTester $I): void
    {
        $I->loginAsClown();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());

        $I->see('Spieltermin (regulär)', 'h4');
        $I->see('Seniorenheim am See', Locator::contains('table tr', text: 'Wo'));
        $I->see('15.01.2036 nachmittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('14:45', Locator::contains('table tr', text: 'Treffen'));
        $I->see('15:30 - 17:30', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Hannah Hosianna | Uwe Popuwe', Locator::contains('table tr', text: 'Spielende Clowns'));
        $I->see('Maria Popia', Locator::contains('table tr', text: 'Springer'));
        $I->see('nein', Locator::contains('table tr', text: 'wiederkehrend'));
    }

    public function showRecurring(FunctionalTester $I): void
    {
        $this->recurringDateFactory->create(
            rhythm: 'weekly',
            every: 3,
            dayOfWeek: 'Thursday',
            venue: $this->playDate->getVenue(),
            playDates: [$this->playDate],
        );
        $I->loginAsClown();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());

        $I->see('Donnerstags alle 3 Wochen', Locator::contains('table tr', text: 'wiederkehrend'));
    }
}
