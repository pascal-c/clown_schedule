<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class EditBlockedClownsCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(name: 'Torte');
        $this->clownFactory->create(name: 'Sahne');
        $this->clownFactory->create(name: 'Döner');
        $this->clownFactory->create(name: 'Pizza');
    }

    public function edit(AdminTester $I)
    {
        $I->loginAsAdmin();
        $I->click('Clowns', 'nav');
        $I->click('Gesperrte Clowns bearbeiten', Locator::contains('tr', 'Torte'));
        $I->checkMultipleOption('Gesperrte Clowns', ['Döner', 'Pizza']);
        $I->click('speichern');
        $I->see('Gesperrte Clowns für Torte wurden erfolgreich gespeichert.');
        $I->see('Döner, Pizza', Locator::contains('table tr', 'Torte'));

        // remove all blocked clowns again
        $I->click('Gesperrte Clowns bearbeiten', Locator::contains('tr', 'Torte'));
        $I->uncheckMultipleOption('Gesperrte Clowns', ['Döner', 'Pizza']);
        $I->click('speichern');
        $I->see('Gesperrte Clowns für Torte wurden erfolgreich gespeichert.');
        $I->dontSee('Döner, Pizza', Locator::contains('table tr', 'Torte'));
    }
}
