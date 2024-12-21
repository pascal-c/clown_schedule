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
        $this->clownFactory->create(name: 'Elena');
        $this->clownFactory->create(name: 'Timo');
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/venues');
        $I->click('Spielort anlegen');
        $I->see('Spielort anlegen');
        $I->fillField('Kurzname', 'DRK Leipzig');
        $I->fillField('Offizieller Name', 'Deutsches Rotes Kreuz Leipzig');
        $I->fillField('Straße und Hausnummer', 'Teststr. 34');
        $I->fillField('PLZ', '04277');
        $I->fillField('Ort', 'Leipzig');
        $I->checkMultipleOption('Verantwortliche Clowns', ['Erika', 'Elena']);
        $I->checkMultipleOption('Gesperrte Clowns', ['Timo']);
        $I->selectOption('venue_form[daytimeDefault]', TimeSlotPeriodInterface::PM);
        $I->selectTimeOption('venue_form[meetingTime]', '09:30');
        $I->selectTimeOption('venue_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('venue_form[playTimeTo]', '12:00');
        $I->fillField('Bemerkungen', 'Tolle Einrichtung!');
        $I->fillField('URL (für weitere Infos zur Einrichtung)', 'www.clowns-und-clowns.de');
        $I->click('Spielort speichern');

        $I->see('DRK Leipzig', 'h4');
        $I->see('Deutsches Rotes Kreuz Leipzig', Locator::contains('table tr', text: 'Offizieller Name'));
        $I->see('Teststr. 34, 04277 Leipzig', Locator::contains('table tr', text: 'Adresse'));
        $I->see('Erika | Elena', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
        $I->see('Timo', Locator::contains('table tr', text: 'Gesperrte Clowns'));
        $I->see('nachmittags', Locator::contains('table tr', text: 'Standard Tageszeit für Spieltermine'));
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 12:00', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Tolle Einrichtung!', Locator::contains('table tr', text: 'Bemerkungen'));
        $I->see('www.clowns-und-clowns.de', Locator::contains('table tr', text: 'Link mit weiteren Infos'));
    }
}
