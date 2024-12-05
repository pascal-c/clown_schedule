<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Attribute\Before;
use Codeception\Util\Locator;

class CreateSpecialPlayDateCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->clownFactory->create(name: 'Thorsten');
    }

    protected function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/schedule');
        $I->click('Zusatztermin anlegen');
        $I->see('Zusatztermin anlegen', 'h4');
        $I->fillField('Titel', 'Kindergeburtstag');
        $I->fillField('Datum', '1999-11-02');
        $I->selectOption('special_play_date_form[daytime]', TimeSlotPeriodInterface::AM);
        $I->selectTimeOption('special_play_date_form[meetingTime]', '09:30');
        $I->selectTimeOption('special_play_date_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('special_play_date_form[playTimeTo]', '12:00');
        $I->click('Zusatztermin speichern');
    }

    #[Before('create')]
    public function createWithoutFee(AdminTester $I): void
    {
        $I->see('Zusatztermin wurde erfolgreich angelegt');
        $I->click('Überspringen');

        $I->amGoingTo('test, if the new special date is being showed correctly in schedule');
        $I->amOnPage('/schedule/1999-11');
        $I->see('Kindergeburtstag', Locator::contains('.row', text: '02. Nov'));
        $I->dontSee('Thorsten', Locator::contains('.row', text: '02. Nov'));
    }

    #[Before('create')]
    public function createWithFee(AdminTester $I): void
    {
        $I->fillField('Honorar Öffis', '150,00');
        $I->fillField('Honorar PKW', '142,00');
        $I->fillField('Kilometerpauschale', '0,40');
        $I->fillField('Kilometer', '300');
        $I->uncheckOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->amGoingTo('test, if the new special date is being showed correctly in show view');
        $I->see('Yes, Honorar gespeichert');
        $I->see('Kindergeburtstag', Locator::contains('table tr', text: 'Wo'));
        $I->see('02.11.1999 vormittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 12:00', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see(html_entity_decode('150,00&nbsp;€ / 142,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar Öffis / PKW'));
        $I->see(html_entity_decode('0,40&nbsp;€ x 300 km (Hin- und Rück) = 120,00&nbsp;€ (für nur einen Clown)'), Locator::contains('table tr', text: 'Kilometergeld'));
    }
}
