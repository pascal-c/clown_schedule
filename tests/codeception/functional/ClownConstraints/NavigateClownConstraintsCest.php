<?php

namespace App\Tests\FunctionalClownConstraints;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;

class NavigateClownConstraintsCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->clownFactory->create(name: 'Paulo');
    }

    public function navigateAsAdmin(AdminTester $I): void
    {
        Functional::$now = '2024-12-30';
        $I->loginAsAdmin();
        $I->click('Wünsche', '.nav');

        // when feature is disabled the menu entry is not shown
        $I->dontSee('Spielortpräferenzen', '.nav');
        $this->configFactory->update(featureClownVenuePreferencesActive: true);
        $I->click('Wünsche', '.nav');

        $I->see('Wünsche und Verfügbarkeiten', '.nav .nav-link.active');
        $I->see('Wünsche Dez. 2024 ', 'h4'); // this is the index page of wishes

        $I->click('Spielortpräferenzen', '.nav');
        $I->see('Spielortpräferenzen', '.nav .nav-link.active');
        $I->see('nur meine anzeigen'); // this is the index page of venue preferences

        $I->click('Dashboard', '.nav');
        $I->click('Wünsche');
        $I->see('Spielortpräferenzen', '.nav .nav-link.active'); // still on venue preferences
    }

    public function navigateAsClown(FunctionalTester $I): void
    {
        $this->configFactory->update(featureClownVenuePreferencesActive: true);
        Functional::$now = '2024-12-30';

        // when logged in as clown I land on detail wishes page for current clown
        $I->loginAsClown('Emily');
        $I->click('Wünsche', '.nav');
        $I->see('Wünsche und Verfügbarkeiten', '.nav .nav-link.active');
        $I->see('Wünsche Emily Dez. 2024 ', 'h4'); // this is the show page of wishes for the current clown

        // when navigating to venue preferences I land on detail venue preferences page for current clown
        $I->click('Spielortpräferenzen', '.nav');
        $I->see('Spielortpräferenzen', '.nav .nav-link.active');
        $I->see('Spielort Präferenzen Emily', 'h4');
        $I->see('alle anzeigen');

        // when navigating to another main section and back the last submenu (venue preferences) is preserved
        $I->click('Dashboard', '.nav');
        $I->click('Wünsche', '.nav');
        $I->see('Spielortpräferenzen', '.nav .nav-link.active'); // still on venue preferences

        // when navigating back to wishes I land on detail wishes page for last selected clown
        $I->click('alle anzeigen');
        $I->click('Paulo');
        $I->see('Spielort Präferenzen Paulo', 'h4');
        $I->click('Wünsche und Verfügbarkeiten', '.nav');
        $I->see('Wünsche Paulo Dez. 2024 ', 'h4');
    }
}
