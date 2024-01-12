<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class CreateVenueCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(name: 'Erika');
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/venues');
        $I->click('Spielort anlegen');
        $I->see('Spielort anlegen');
        $I->fillField('Name', 'DRK Leipzig');
        $I->fillField('Straße und Hausnummer', 'Teststr. 34');
        $I->fillField('PLZ', '04277');
        $I->fillField('Ort', 'Leipzig');
        $I->fillField('Ansprechperson', 'Anneliese');
        $I->fillField('Telefon', '01234567');
        $I->fillField('Email', 'anneliese@drk.de');
        $I->fillField('Honorar Öffis', '145,00');
        $I->fillField('Honorar PKW', '142,00');
        $I->fillField('Kilometerpauschale', '0,35');
        $I->fillField('Kilometer', '100');
        $I->checkOption('Erika');
        $I->selectOption('venue_form[daytimeDefault]', TimeSlotPeriodInterface::PM);
        $I->selectTimeOption('venue_form[meetingTime]', '09:30');
        $I->selectTimeOption('venue_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('venue_form[playTimeTo]', '12:00');
        $I->fillField('Bemerkungen', 'Tolle Einrichtung!');
        $I->fillField('URL (für weitere Infos zur Einrichtung)', 'www.clowns-und-clowns.de');
        $I->click('Spielort speichern');

        $I->see('DRK Leipzig', 'h4');
        $I->see('Teststr. 34, 04277 Leipzig', Locator::contains('table tr', text: 'Adresse'));
        $I->see('Anneliese', Locator::contains('table tr', text: 'Ansprechperson'));
        $I->see('01234567', Locator::contains('table tr', text: 'Telefon'));
        $I->see('anneliese@drk.de', Locator::contains('table tr', text: 'Email'));
        $I->see('Erika', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
        $I->see('nachmittags', Locator::contains('table tr', text: 'Standard Tageszeit für Spieltermine'));
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 12:00', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see(html_entity_decode('145,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar Öffis'));
        $I->see(html_entity_decode('142,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar PKW'));
        $I->see(html_entity_decode('0,35&nbsp;€ x 100 km (Hin- und Rück) = 35,00&nbsp;€ (pro Clown) '), Locator::contains('table tr', text: 'Kilometergeld'));
        $I->see('Tolle Einrichtung!', Locator::contains('table tr', text: 'Bemerkungen'));
        $I->see('www.clowns-und-clowns.de', Locator::contains('table tr', text: 'Link mit weiteren Infos'));
    }
}
