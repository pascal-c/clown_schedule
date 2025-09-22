<?php

namespace App\Tests\Functional\Config;

use App\Tests\Functional\AbstractCest;
use App\Tests\Step\Functional\AdminTester;

class ConfigCalculationCest extends AbstractCest
{
    public function featureUseCalculation(AdminTester $I): void
    {
        $I->loginAsAdmin();

        // calculation is activated by default
        $I->click('Dashboard');
        $I->see('Wünsche verwalten');

        $I->click('Einstellungen');
        $I->click('Berechnung', '.nav');

        $I->see('Feature “Max. Spielanzahl pro Woche”');
        $I->uncheckOption('Automatische Berechnung');
        $I->click('speichern');

        // all calculation features are hidden when calculation itself is deactivated
        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->dontSee('Feature “Max. Spielanzahl pro Woche”');

        $I->click('Dashboard');
        $I->dontSee('Wünsche verwalten');
    }

    public function featureMaxPlaysPerWeek(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Einstellungen');
        $I->click('Berechnung', '.nav');

        $I->checkOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->seeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');

        $I->amGoingTo('make sure that the feature is really enabled');
        $I->click('Wünsche');
        $I->click('Nein');
        $I->see('Gewünschte maximale Anzahl Spiele pro Woche');

        $I->amGoingTo('disable the feature again');
        $I->click('Einstellungen');
        $I->click('Berechnung', '.nav');
        $I->uncheckOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');
        $I->dontSeeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');
    }
}
