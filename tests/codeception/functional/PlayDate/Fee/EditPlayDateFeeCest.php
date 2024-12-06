<?php

namespace App\Tests\Functional\PlayDate\Fee;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\PlayDateType;
use Codeception\Attribute\Before;
use Codeception\Attribute\Depends;
use Codeception\Util\Locator;

class EditPlayDateFeeCest extends AbstractCest
{
    private int $playDateId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playDateId = $this->playDateFactory->create(
            title: 'Zusatztermin',
            type: PlayDateType::SPECIAL,
        )->getId();
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDateId);

        $I->see('Zusatztermin', 'h4');
        $I->see('?', Locator::contains('table tr', text: 'Honorar'));
        $I->click('Honorar anlegen', Locator::contains('table tr', text: 'Honorar'));

        $I->fillField('Honorar Öffis', '150,00');
        $I->fillField('Honorar PKW', '142,00');
        $I->fillField('Kilometerpauschale', '0,40');
        $I->fillField('Kilometer', '300');
        $I->checkOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->see('Yes, Honorar gespeichert');
        $I->see(html_entity_decode('150,00&nbsp;€ / 142,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar Öffis / PKW'));
        $I->see(html_entity_decode('0,40&nbsp;€ x 300 km (Hin- und Rück) = 120,00&nbsp;€ (pro Clown)'), Locator::contains('table tr', text: 'Kilometergeld'));
    }

    #[Before('create')]
    #[Depends('create')]
    public function edit(AdminTester $I): void
    {
        $I->click('bearbeiten', Locator::contains('table tr', text: 'Honorar'));

        $I->fillField('Honorar Öffis', '160,00');
        $I->fillField('Honorar PKW', '150,00');
        $I->fillField('Kilometerpauschale', '0,50');
        $I->fillField('Kilometer', '200');
        $I->uncheckOption('Kilometergeld für beide Clowns');
        $I->click('Honorar speichern');

        $I->see('Yes, Honorar gespeichert');
        $I->see(html_entity_decode('160,00&nbsp;€ / 150,00&nbsp;€'), Locator::contains('table tr', text: 'Honorar Öffis / PKW'));
        $I->see(html_entity_decode('0,50&nbsp;€ x 200 km (Hin- und Rück) = 100,00&nbsp;€ (für nur einen Clown)'), Locator::contains('table tr', text: 'Kilometergeld'));
    }
}
