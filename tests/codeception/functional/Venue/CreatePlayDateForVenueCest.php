<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;

class CreatePlayDateForVenueCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venueFactory->create(name: 'Wichern', isSuper: true, daytimeDefault: TimeSlotPeriodInterface::ALL);
    }

    public function createPlayDate(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte', 'nav .nav-link');
        $I->click('Wichern');
        $I->click('Spieltermin anlegen');
        $I->fillField('Datum', '1999-11-02');
        $I->seeInField('regular_play_date_form[daytime]', TimeSlotPeriodInterface::ALL);
        $I->seeCheckboxIsChecked('ist ein Super-Spieltermin? (nur relevant für Statistik)');
        $I->click('Spieltermin speichern');
        $I->see('Spieltermin (regulär) wurde erfolgreich angelegt.', '.alert-success');
        $I->dontSee('02.11.1999');

        $I->click('1999', '.nav-link');
        $I->see('02.11.1999');
    }
}
