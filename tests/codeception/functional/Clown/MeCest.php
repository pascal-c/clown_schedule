<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class MeCest extends AbstractCest
{
    public function edit(FunctionalTester $I)
    {
        $I->loginAsClown('Tortie');
        $I->click('Tortie', 'nav');
        $I->fillField('Name', 'Tortilie');
        $I->fillField('Email', 'tortilie@example.com');
        $I->fillField('Telefon', '0123456789');
        $I->selectOption('form input[name=me_form\[gender\]]', 'female');
        $I->click('speichern');

        $I->see('Deine Daten wurden erfolgreich gespeichert.', '.alert-success');
        $I->seeInField('Name', 'Tortilie');
        $I->seeInField('Email', 'tortilie@example.com');
        $I->seeInField('Telefon', '0123456789');
        $I->seeOptionIsSelected('form input[name=me_form\[gender\]]', 'female');
    }
}
