<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class RegisterForTrainingCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2036-01-15');
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

    public function test(FunctionalTester $I): void
    {
        Functional::$now = '2036-01-15';

        $I->amGoingTo('register myself for the training');
        $I->loginAsClown('Emilio');
        $I->amOnPage('/play_dates/'.$this->playDateId);
        $I->click('Zum Training anmelden');
        $I->see('Du bist jetzt für das Training angemeldet.');
        $I->see('Emilio', Locator::contains('table tr', text: 'Spielende Clowns'));

        $I->amGoingTo('unregister from the training');
        $I->click('Vom Training abmelden');
        $I->see('Du bist jetzt für das Training abgemeldet.');
        $I->dontSee('Emilio', Locator::contains('table tr', text: 'Spielende Clowns'));
    }

    public function testWithPastPlayDate(FunctionalTester $I): void
    {
        Functional::$now = '2036-01-16';

        $I->amGoingTo('register myself for the training');
        $I->loginAsClown('Emilio');
        $I->amOnPage('/play_dates/'.$this->playDateId);
        $I->dontSee('Zum Training anmelden');
    }
}
