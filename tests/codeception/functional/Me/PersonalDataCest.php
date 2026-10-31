<?php

namespace App\Tests\Functional\Me;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class PersonalDataCest extends AbstractCest
{
    public function edit(FunctionalTester $I)
    {
        $I->loginAsClown('Tortie');
        $I->click('Tortie', 'nav');
        $I->fillField('Name', 'Tortilie');
        $I->fillField('Email', 'tortilie@example.com');
        $I->fillField('Telefon', '0123456789');
        $I->selectOption('form input[name=personal_data_form\[gender\]]', 'female');
        $I->click('speichern');

        $I->see('Deine Daten wurden erfolgreich gespeichert.', '.alert-success');
        $I->seeInField('Name', 'Tortilie');
        $I->seeInField('Email', 'tortilie@example.com');
        $I->seeInField('Telefon', '0123456789');
        $I->seeOptionIsSelected('form input[name=personal_data_form\[gender\]]', 'female');
    }
}
