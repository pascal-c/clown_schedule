<?php

namespace App\Tests\Functional\Config;

use App\Tests\Functional\AbstractCest;
use App\Tests\Step\Functional\AdminTester;

class ConfigInvoiceManagementCest extends AbstractCest
{
    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Einstellungen');
        $I->click('Rechnungsverwaltung');

        $I->checkOption('Feature "Rechnungsverwaltung für Clownshonorare"');
        $I->checkOption('Feature "Rechnungsstellung Clowns"');
        $I->checkOption('Feature "Rechnungsbezahlung Clowns"');
        $I->fillField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->fillField('Bezeichnung für alternatives Honorar', 'Alternatives Honorar');
        $I->click('speichern');

        $I->seeInField('Bezeichnung für Standard-Honorar', 'Standard-Honorar');
        $I->seeInField('Bezeichnung für alternatives Honorar', 'Alternatives Honorar');
        $I->seeCheckboxIsChecked('Feature "Rechnungsverwaltung für Clownshonorare"');
        $I->seeCheckboxIsChecked('Feature "Rechnungsstellung Clowns"');
        $I->seeCheckboxIsChecked('Feature "Rechnungsbezahlung Clowns"');
    }
}
