<?php

namespace App\Tests\Functional\Config;

use App\Tests\Functional\AbstractCest;
use App\Tests\Step\Functional\AdminTester;
use DateTimeImmutable;

class ConfigGeneralCest extends AbstractCest
{
    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Einstellungen');

        $I->fillField('Zusatztermine Link', 'https://www.example.com');
        $I->selectOption('Bundesland', 'Sachsen');
        $I->uncheckOption('Feature "Spieltermine tauschen"');
        $I->fillField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->fillField('Bezeichnung für alternatives Honorar', '');
        $I->click('speichern');

        $I->see('Yep! Einstellungen wurden gespeichert.', '.alert-success');
        $I->seeInField('Zusatztermine Link', 'https://www.example.com');
        $I->dontSeeCheckboxIsChecked('Feature "Spieltermine tauschen"');
        $I->seeInField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->seeInField('Bezeichnung für alternatives Honorar', '');
        $I->seeInField('Bundesland', 'Sachsen');
    }

    public function featurePlayDateChangeRequestsActive(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $playDate = $this->playDateFactory->create(playingClowns: [$I->getCurrentUser()], date: new DateTimeImmutable('2123-10-01'));

        $I->amOnPage('/play_dates/'.$playDate->getId());
        $I->see('Spieltermin tauschen', 'a');

        $I->click('Einstellungen');
        $I->uncheckOption('Feature "Spieltermine tauschen"');
        $I->click('speichern');

        $I->amOnPage('/play_dates/'.$playDate->getId());
        $I->dontSee('Spieltermin tauschen', 'a');
    }

    public function featureTeamsActive(AdminTester $I): void
    {
        $this->venueFactory->create(name: 'Superheim');
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->dontSee('Clownsteam');
        $I->click('Superheim');
        $I->dontSee('Clownsteam');

        $I->click('Einstellungen');
        $I->click('Spielplanberechnung', '.nav');
        $I->dontSee('Punkte pro zugeordnetem Clown, der nicht im Team des Spielortes ist');

        $I->click('Allgemein', '.nav');
        $I->checkOption('Feature "Clownsteams"');
        $I->click('speichern');

        $I->click('Spielplanberechnung', '.nav');
        $I->see('Punkte pro zugeordnetem Clown, der nicht im Team des Spielortes ist');
        $I->click('Spielorte');
        $I->see('Clownsteam');
        $I->click('Superheim');
        $I->see('Clownsteam');
    }
}
