<?php

namespace App\Tests\Functional\Venue\Fee;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class CreateVenueFeeCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $venue = $this->venueFactory->create(name: 'Spargelheim');
        $this->feeFactory->create(
            venue: $venue,
            feeByPublicTransport: 145.50,
            feeByCar: 133.33,
            kilometers: 200,
            feePerKilometer: 0.31,
            kilometersFeeForAllClowns: false,
            validFrom: '2024-11-02',
        );
    }

    public function createSuccessfully(AdminTester $I): void
    {
        Functional::$now = '2024-11-05';

        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Honorare');
        $I->click('Honorar anlegen');

        $I->see('Neues Honorar für Spargelheim', 'h5');
        $I->seeInField('Honorar Öffis', '145,50');
        $I->seeInField('Honorar PKW', '133,33');
        $I->seeInField('Kilometer', '200');
        $I->seeInField('Kilometerpauschale', '0,31');
        $I->dontSeeCheckboxIsChecked('Kilometergeld für beide Clowns');

        $I->fillField('Gültig ab', '2024-11-03');
        $I->fillField('Honorar Öffis', '150,00');
        $I->fillField('Honorar PKW', '142,00');
        $I->fillField('Kilometerpauschale', '0,40');
        $I->fillField('Kilometer', '300');
        $I->checkOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->see('Großartig! Neues Honorar erfolgreich angelegt!');
        $I->see(html_entity_decode('150,00&nbsp;€'), Locator::contains('table tbody tr:first-child', text: '03.11.2024'));
        $I->see(html_entity_decode('142,00&nbsp;€'), Locator::contains('table tbody tr:first-child', text: '03.11.2024'));
        $I->see(html_entity_decode('0,40&nbsp;€ x 300 km (Hin- und Rück) = 120,00&nbsp;€ (pro Clown)'), Locator::contains('table tr:first-child', text: '03.11.2024'));
    }

    public function createFailureNewValidDateOlderThanExisting(AdminTester $I): void
    {
        Functional::$now = '2024-11-05';

        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Honorare');
        $I->click('Honorar anlegen');

        $I->see('Neues Honorar für Spargelheim', 'h5');

        $I->fillField('Gültig ab', '2024-11-02');
        $I->fillField('Honorar Öffis', '150,00');
        $I->click('Honorar speichern');
        $I->seeInField('Honorar Öffis', '150,00');

        $I->see('Das können wir so nicht machen!');
        $I->see('Dieser Wert sollte größer als 02.11.2024', Locator::contains('div', text: 'Gültig ab'));
    }

    public function createFailureNewValidDateOlderThanCurrentMonth(AdminTester $I): void
    {
        Functional::$now = '2024-12-01';

        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Honorare');
        $I->click('Honorar anlegen');

        $I->see('Neues Honorar für Spargelheim', 'h5');

        $I->fillField('Gültig ab', '2024-11-03');
        $I->fillField('Honorar Öffis', '150,00');
        $I->click('Honorar speichern');
        $I->seeInField('Honorar Öffis', '150,00');

        $I->see('Das können wir so nicht machen!');
        $I->see('Dieser Wert sollte größer oder gleich 01.12.2024', Locator::contains('div', text: 'Gültig ab'));
    }
}
