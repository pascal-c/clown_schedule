<?php

namespace App\Tests\Functional;

use App\Tests\Step\Functional\AdminTester;

class ConfigCest extends AbstractCest
{
    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Einstellungen');

        $I->fillField('Zusatztermine Link', 'https://www.example.com');
        $I->checkOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');

        $I->seeInField('Zusatztermine Link', 'https://www.example.com');
        $I->seeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');

        $I->amGoingTo('make sure that the feature is really enabled');
        $I->click('Fehlzeiten');
        $I->click('Nein');
        $I->see('Gewünschte maximale Anzahl Spiele pro Woche');

        $I->amGoingTo('disable the feature again');
        $I->click('Einstellungen');
        $I->uncheckOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');
        $I->dontSeeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');
    }
}
