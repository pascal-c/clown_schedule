<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class CreateSpecialPlayDateCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(name: 'Erika');
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/schedule');
        $I->click('Sondertermin anlegen');
        $I->see('Spieltermin anlegen', 'h4');
        $I->fillField('Titel', 'Kindergeburtstag');
        $I->fillField('Datum', '1999-11-02');
        $I->selectOption('special_play_date_form[daytime]', TimeSlotPeriodInterface::AM);
        $I->selectTimeOption('special_play_date_form[meetingTime]', '09:30');
        $I->selectTimeOption('special_play_date_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('special_play_date_form[playTimeTo]', '12:00');
        $I->click('Sondertermin speichern');
        $I->see('Spieltermin wurde erfolgreich angelegt');

        $I->amGoingTo('test, if the new special date is being showed correctly in schedule');
        $I->amOnPage('/schedule/1999-11');
        $I->see('Kindergeburtstag', Locator::contains('.row', text: '02. Nov'));
    }
}
