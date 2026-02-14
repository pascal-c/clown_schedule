<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class IndexCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(name: 'Torte', email: 'torte@torte.de');
        $this->clownFactory->create(name: 'Sahne', email: 'sahne@torte.de', phone: '0123456789');
    }

    public function edit(AdminTester $I)
    {
        $I->loginAsAdmin();
        $I->click('Clowns', 'nav');
        $I->see('torte@torte.de', Locator::contains('table tr', 'Torte'));
        $I->dontSee('0123456789', Locator::contains('table tr', 'Torte'));

        $I->see('sahne@torte.de', Locator::contains('table tr', 'Sahne'));
        $I->see('0123456789', Locator::contains('table tr', 'Sahne'));
    }
}
