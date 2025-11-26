<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class ShowSpecialPlayDateCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2124-01-15');
        $daytime = TimeSlotPeriodInterface::PM;

        $this->playDateId = $this->playDateFactory->create(
            title: 'Spezialtermin',
            type: PlayDateType::SPECIAL,
            date: $date,
            daytime: $daytime,
            meetingTime: '14:45',
            playTimeFrom: '15:30',
            playTimeTo: '17:30',
            playingClowns: [$this->clownFactory->create(name: 'Antoinette'), $this->clownFactory->create(name: 'Jean-Pierre')],
        )->getId();

        $this->substitutionFactory->create(date: $date, daytime: $daytime, clown: $this->clownFactory->create(name: 'Marie Louise'));
    }

    public function tryToTest(FunctionalTester $I): void
    {
        $I->loginAsClown();
        $I->amOnPage('/play_dates/'.$this->playDateId);

        $I->see('Zusatztermin', 'h4');
        $I->see('Spezialtermin', Locator::contains('table tr', text: 'Wo'));
        $I->see('15.01.2124 nachmittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('14:45', Locator::contains('table tr', text: 'Treffen'));
        $I->see('15:30 - 17:30', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Antoinette | Jean-Pierre', Locator::contains('table tr', text: 'Spielende Clowns'));
        $I->see('Marie Louise', Locator::contains('table tr', text: 'Springer'));
    }
}
