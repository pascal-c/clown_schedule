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
        $I->selectOption('Bundesland', 'Sachsen');
        $I->fillField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->fillField('Bezeichnung für alternatives Honorar', '');
        $I->click('speichern');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->seeInField('Zusatztermine Link', 'https://www.example.com');
        $I->seeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');
        $I->seeInField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->seeInField('Bezeichnung für alternatives Honorar', '');
        $I->seeInField('Bundesland', 'Sachsen');

        $I->amGoingTo('make sure that the feature is really enabled');
        $I->click('Wünsche');
        $I->click('Nein');
        $I->see('Gewünschte maximale Anzahl Spiele pro Woche');

        $I->amGoingTo('disable the feature again');
        $I->click('Einstellungen');
        $I->uncheckOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');
        $I->dontSeeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');
    }
}
