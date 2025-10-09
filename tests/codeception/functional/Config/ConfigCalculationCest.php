<?php

namespace App\Tests\Functional\Config;

use App\Tests\Functional\AbstractCest;
use App\Tests\Step\Functional\AdminTester;

class ConfigCalculationCest extends AbstractCest
{
    public function featureUseCalculation(AdminTester $I): void
    {
        $I->loginAsAdmin();

        // calculation features are activated by default
        $I->click('Dashboard', '.nav');
        $I->see('Wünsche verwalten');
        $I->click('Spielplan', '.nav');
        $I->seeLink('Spielplan berechnen');

        $I->click('Einstellungen');
        $I->click('Berechnung', '.nav');

        $I->see('Feature “Max. Spielanzahl pro Woche”');
        $I->see('Verantwortlichen Clown als 1. Clown zuordnen');
        $I->uncheckOption('Automatische Berechnung');
        $I->click('speichern');

        // all calculation features are hidden when calculation itself is deactivated
        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->dontSee('Feature “Max. Spielanzahl pro Woche”');
        $I->dontSee('Verantwortlichen Clown als 1. Clown zuordnen');

        // make sure calculation features are really deactivated
        $I->click('Dashboard');
        $I->dontSee('Wünsche verwalten');
        $I->click('Spielplan', '.nav');
        $I->dontSee('Spielplan berechnen');
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

    public function ratingPoints(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Einstellungen');
        $I->click('Berechnung', '.nav');

        $I->seeInField('Punkte pro fehlender Zuordnung', '100');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '1');
        $I->seeInField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '2');
        $I->seeInField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '10');

        $I->fillField('Punkte pro fehlender Zuordnung', '101');
        $I->fillField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '3');
        $I->fillField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '4');
        $I->fillField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '11');

        $I->click('speichern');

        $I->seeInField('Punkte pro fehlender Zuordnung', '101');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '3');
        $I->seeInField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '4');
        $I->seeInField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '11');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
    }
}
