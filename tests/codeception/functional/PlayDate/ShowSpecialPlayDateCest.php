<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class ShowSpecPlayDateCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2124-01-15');
        $daytime = TimeSlotPeriodInterface::PM;

        $this->playDateId = $this->playDateFactory->create(
            title: 'Spezialtermin',
            date: $date,
            daytime: $daytime,
            meetingTime: '14:45',
            playTimeFrom: '15:30',
            playTimeTo: '17:30',
            playingClowns: [$this->clownFactory->create(name: 'Hannah'), $this->clownFactory->create(name: 'Uwe')]
        )->getId();

        $this->substitutionFactory->create(date: $date, daytime: $daytime, clown: $this->clownFactory->create(name: 'Maria'));
    }

    public function tryToTest(FunctionalTester $I): void
    {
        $I->loginAsClown();
        $I->amOnPage('/play_dates/'.$this->playDateId);

        $I->see('Spezialtermin', Locator::contains('table tr', text: 'Wo'));
        $I->see('15.01.2124 nachmittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('14:45', Locator::contains('table tr', text: 'Treffen'));
        $I->see('15:30 - 17:30', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Hannah | Uwe', Locator::contains('table tr', text: 'Spielende Clowns'));
        $I->see('Maria', Locator::contains('table tr', text: 'Springer'));
    }
}
