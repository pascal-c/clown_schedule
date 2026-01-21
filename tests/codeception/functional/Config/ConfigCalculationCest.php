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
        $I->click('Spielplanberechnung', '.nav');

        $I->see('Feature “Max. Spielanzahl pro Woche”');
        $I->see('Feature “Verantwortliche Clowns“');
        $I->uncheckOption('Automatische Spielplanberechnung');
        $I->click('speichern');

        // all calculation features are hidden when calculation itself is deactivated
        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->dontSee('Feature “Max. Spielanzahl pro Woche”');
        $I->dontSee('Feature “Verantwortlichen Clowns“');

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
        $I->click('Spielplanberechnung', '.nav');

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
        $I->click('Spielplanberechnung', '.nav');
        $I->uncheckOption('Feature “Max. Spielanzahl pro Woche”');
        $I->click('speichern');
        $I->dontSeeCheckboxIsChecked('Feature “Max. Spielanzahl pro Woche”');
    }

    public function featureVenuePreferences(AdminTester $I): void
    {
        $I->loginAsAdmin();

        // the feature is disabled by default
        $I->click('Wünsche', '.nav');
        $I->dontSee('Spielortpräferenzen', '.nav');

        $I->click('Einstellungen');
        $I->click('Spielplanberechnung', '.nav');

        $I->checkOption('Feature “Spielortpräferenzen der Clowns”');
        $I->click('speichern');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->seeCheckboxIsChecked('Feature “Spielortpräferenzen der Clowns”');

        $I->amGoingTo('make sure that the feature is really enabled');
        $I->click('Wünsche');
        $I->see('Spielortpräferenzen', '.nav');

        $I->amGoingTo('disable the feature again');
        $I->click('Einstellungen');
        $I->click('Spielplanberechnung', '.nav');
        $I->uncheckOption('Feature “Spielortpräferenzen der Clowns”');
        $I->click('speichern');
        $I->dontSeeCheckboxIsChecked('Feature “Spielortpräferenzen der Clowns”');
    }

    public function ratingPoints(AdminTester $I): void
    {
        $this->configFactory->update(featureTeamsActive: true);
        $I->loginAsAdmin();
        $I->click('Einstellungen');
        $I->click('Spielplanberechnung', '.nav');

        $I->seeInField('Punkte pro fehlender Zuordnung', '100');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '1');
        $I->seeInField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '2');
        $I->seeInField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '10');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nicht im Team des Spielortes ist', '30');
        $I->seeInField('Punkte pro Spielortpräferenz "wenn\'s gar nicht anders geht"', '10');
        $I->seeInField('Punkte pro Spielortpräferenz "na gut"', '4');
        $I->seeInField('Punkte pro Spielortpräferenz "ok"', '2');
        $I->seeInField('Punkte pro Spielortpräferenz "sehr gerne"', '1');
        $I->seeInField('Punkte pro Spielortpräferenz "au ja, unbedingt!"', '0');

        $I->fillField('Punkte pro fehlender Zuordnung', '101');
        $I->fillField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '3');
        $I->fillField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '4');
        $I->fillField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '11');
        $I->fillField('Punkte pro zugeordnetem Clown, der nicht im Team des Spielortes ist', '40');
        $I->fillField('Punkte pro Spielortpräferenz "wenn\'s gar nicht anders geht"', '11');
        $I->fillField('Punkte pro Spielortpräferenz "na gut"', '5');
        $I->fillField('Punkte pro Spielortpräferenz "ok"', '3');
        $I->fillField('Punkte pro Spielortpräferenz "sehr gerne"', '2');
        $I->fillField('Punkte pro Spielortpräferenz "au ja, unbedingt!"', '1');

        $I->click('speichern');

        $I->seeInField('Punkte pro fehlender Zuordnung', '101');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss', '3');
        $I->seeInField('Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt', '4');
        $I->seeInField('Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird', '11');
        $I->seeInField('Punkte pro zugeordnetem Clown, der nicht im Team des Spielortes ist', '40');
        $I->seeInField('Punkte pro Spielortpräferenz "wenn\'s gar nicht anders geht"', '11');
        $I->seeInField('Punkte pro Spielortpräferenz "na gut"', '5');
        $I->seeInField('Punkte pro Spielortpräferenz "ok"', '3');
        $I->seeInField('Punkte pro Spielortpräferenz "sehr gerne"', '2');
        $I->seeInField('Punkte pro Spielortpräferenz "au ja, unbedingt!"', '1');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
    }
}
