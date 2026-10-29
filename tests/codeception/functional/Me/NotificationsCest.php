<?php

namespace App\Tests\Functional\Me;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class NotificationsCest extends AbstractCest
{
    public function edit(FunctionalTester $I)
    {
        $I->loginAsClown('Tortie');
        $I->click('Tortie', 'nav');
        $I->click('Benachrichtigungen', 'nav');

        // when invoicing clowns ist not configured...
        $I->dontSee('Dashboard-Hinweis bei zu stellender Rechnung');
        $I->dontSee('Email bei zu stellender Rechnung');
        $I->dontSee('Email-Erinnerung bei zu stellender Rechnung');

        // there is only on checkbox
        $I->seeCheckboxIsChecked('Email bei Fertigstellung des Spielplans');
        $I->uncheckOption('Email bei Fertigstellung des Spielplans');
        $I->click('speichern');

        $I->dontSeeCheckboxIsChecked('Email bei Fertigstellung des Spielplans');

        // when invoicing clowns is configured
        $this->configFactory->update(featureInvoicingClowns: true);
        $I->click('Benachrichtigungen', 'nav');
        $I->seeCheckboxIsChecked('Dashboard-Hinweis bei zu stellender Rechnung');
        $I->dontSeeCheckboxIsChecked('Email bei zu stellender Rechnung');
        $I->seeCheckboxIsChecked('Email-Erinnerung bei zu stellender Rechnung');

        $I->checkOption('Email bei Fertigstellung des Spielplans');
        $I->uncheckOption('Dashboard-Hinweis bei zu stellender Rechnung');
        $I->checkOption('Email bei zu stellender Rechnung');
        $I->uncheckOption('Email-Erinnerung bei zu stellender Rechnung');
        $I->click('speichern');

        $I->seeCheckboxIsChecked('Email bei Fertigstellung des Spielplans');
        $I->dontSeeCheckboxIsChecked('Dashboard-Hinweis bei zu stellender Rechnung');
        $I->seeCheckboxIsChecked('Email bei zu stellender Rechnung');
        $I->dontSeeCheckboxIsChecked('Email-Erinnerung bei zu stellender Rechnung');
    }
}
