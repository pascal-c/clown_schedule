<?php

namespace App\Tests\Functional\Venue\Fee;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;

class NoVenueFeeCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venueFactory->create(name: 'Spargelheim');
        $this->configFactory->update(featureInvoiceManagementClowns: false);
    }

    public function withDeactivatedInovoiceManagement(AdminTester $I): void
    {
        // The fee tab is not visible
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->dontSee('Honorare');
    }
}
