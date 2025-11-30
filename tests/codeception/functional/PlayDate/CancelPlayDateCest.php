<?php

namespace App\Tests\Functional\PlayDate;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class CancelPlayDateCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        Functional::$now = '2036-01-10';

        $date = new DateTimeImmutable('2036-01-11');
        $daytime = TimeSlotPeriodInterface::PM;

        $venue = $this->venueFactory->create(name: 'Seniorenheim am See');
        $this->playDate = $this->playDateFactory->create(
            venue: $venue,
            date: $date,
            daytime: $daytime,
            playingClowns: [$this->clownFactory->create(name: 'Hannah Hosianna'), $this->clownFactory->create(name: 'Uwe Popuwe')]
        );

        $this->substitutionFactory->create(date: $date, daytime: $daytime, clown: $this->clownFactory->create(name: 'Maria Popia'));
    }

    public function cancel(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());

        $I->see('Seniorenheim am See', Locator::contains('table tr', text: 'Wo'));
        $I->see('Hannah Hosianna | Uwe Popuwe', Locator::contains('table tr', text: 'Spielende Clowns'));
        $I->see('Maria Popia', Locator::contains('table tr', text: 'Springer'));

        $I->seeElement('a', ['title' => 'Spieltermin absagen']);
        $I->click('Spieltermin absagen');
        $I->fillField('Kommentar', 'Krankheit');
        $I->click('Termin jetzt absagen');
        $I->see('Der Spieltermin wurde abgesagt.', '.alert-success');

        $I->see('Seniorenheim am See', Locator::contains('table tr', text: 'Wo'));
        $I->see('abgesagt', Locator::contains('table tr', text: 'Wo'));
        $I->see('Hannah Hosianna | Uwe Popuwe', Locator::contains('table tr', text: 'Spielende Clowns'));
        // substitution should be removed
        $I->dontSee('Maria Popia', Locator::contains('table tr', text: 'Springer'));
        $I->see('Krankheit', Locator::contains('table tr', text: 'Wann'));
        $I->dontSeeElement('a', ['title' => 'Spieltermin absagen']);
    }
}
