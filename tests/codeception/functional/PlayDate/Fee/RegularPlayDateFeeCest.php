<?php

namespace App\Tests\Functional\PlayDate\Fee;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\PlayDateType;
use Codeception\Attribute\Before;
use Codeception\Attribute\Depends;
use Codeception\Util\Locator;
use DateTimeImmutable;

class RegularPlayDateFeeCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $venue = $this->venueFactory->create(name: 'Paris');
        $this->feeFactory->create(
            feeAlternative: 12.42,
            feeStandard: 13.14,
            kilometers: 10,
            feePerKilometer: 0.42,
            kilometersFeeForAllClowns: false,
            venue: $venue,
        );
        $this->playDateId = $this->playDateFactory->create(
            date: new DateTimeImmutable('2025-07-07'),
            type: PlayDateType::REGULAR,
            venue: $venue,
        )->getId();
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDateId);

        $I->see('Spieltermin (regulär)', 'h4');
        $I->see(html_entity_decode('13,14&nbsp;€ / 12,42&nbsp;€'), Locator::contains('table tr', text: 'Honorar'));
        $I->click('individuelles Honorar anlegen', Locator::contains('table tr', text: 'Honorar'));

        $I->see('Individuelles Honorar für Paris am 07.07.2025 anlegen', 'h5');
        $I->seeInField('Honorar Öffis', '13,14');
        $I->seeInField('Honorar PKW', '12,42');
        $I->seeInField('Kilometerpauschale', '0,42');
        $I->seeInField('Kilometer', '10');
        $I->dontSeeCheckboxIsChecked('Kilometergeld für beide Clowns');

        $I->fillField('Honorar Öffis', '150,00');
        $I->fillField('Honorar PKW', '142,00');
        $I->fillField('Kilometerpauschale', '0,40');
        $I->fillField('Kilometer', '300');
        $I->checkOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->see('Yes, Honorar gespeichert');
        $I->see(html_entity_decode('150,00&nbsp;€ / 142,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar'));
        $I->see(html_entity_decode('0,40&nbsp;€ x 300 km (Hin- und Rück) = 120,00&nbsp;€ (pro Clown)'), Locator::contains('table tr', text: 'Kilometergeld'));
    }

    #[Before('create')]
    #[Depends('create')]
    public function edit(AdminTester $I): void
    {
        $I->click('bearbeiten', Locator::contains('table tr', text: 'Honorar'));

        $I->see('Individuelles Honorar für Paris am 07.07.2025 bearbeiten', 'h5');
        $I->seeInField('Honorar Öffis', '150,00');
        $I->seeInField('Honorar PKW', '142,00');
        $I->seeInField('Kilometerpauschale', '0,40');
        $I->seeInField('Kilometer', '300');
        $I->seeCheckboxIsChecked('Kilometergeld für beide Clowns');

        $I->fillField('Honorar Öffis', '160,00');
        $I->fillField('Honorar PKW', '150,00');
        $I->fillField('Kilometerpauschale', '0,50');
        $I->fillField('Kilometer', '200');
        $I->uncheckOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->see('Yes, Honorar gespeichert');
        $I->see(html_entity_decode('160,00&nbsp;€ / 150,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar'));
        $I->see(html_entity_decode('0,50&nbsp;€ x 200 km (Hin- und Rück) = 100,00&nbsp;€ (für nur einen Clown)'), Locator::contains('table tr', text: 'Kilometergeld'));
    }
}
