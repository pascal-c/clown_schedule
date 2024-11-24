<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class ShowTrainingCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2124-01-15');
        $daytime = TimeSlotPeriodInterface::PM;

        $this->playDateId = $this->playDateFactory->create(
            title: 'Toller Workshop',
            type: PlayDateType::TRAINING,
            date: $date,
            daytime: $daytime,
            meetingTime: '14:45',
            playTimeFrom: '15:30',
            playTimeTo: '17:30',
            playingClowns: [$this->clownFactory->create(name: 'Hannah'), $this->clownFactory->create(name: 'Uwe')]
        )->getId();
    }

    public function tryToTest(FunctionalTester $I): void
    {
        $I->loginAsClown();
        $I->amOnPage('/play_dates/'.$this->playDateId);

        $I->see('Trainingstermin', 'h4');
        $I->see('Toller Workshop', Locator::contains('table tr', text: 'Wo'));
        $I->see('15.01.2124 nachmittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('14:45', Locator::contains('table tr', text: 'Treffen'));
        $I->see('15:30 - 17:30', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Hannah | Uwe', Locator::contains('table tr', text: 'Spielende Clowns'));
    }
}
