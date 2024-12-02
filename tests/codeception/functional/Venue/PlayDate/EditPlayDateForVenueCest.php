<?php

namespace App\Tests\Functional\Venue\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class EditPlayDateForVenueCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $venue = $this->venueFactory->create(name: 'Wichern', daytimeDefault: TimeSlotPeriodInterface::ALL);
        $this->playDateFactory->create(date: new DateTimeImmutable('2025-02-23'), venue: $venue, isSuper: true);
    }

    public function createPlayDate(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte', 'nav .nav-link');
        $I->click('Wichern');
        $I->click('Spieltermine', '.nav-link');
        $I->click('2025', '.nav-link');
        $I->click('Termin bearbeiten', Locator::contains('div.col-6', text: '23.02.2025'));
        $I->seeInField('regular_play_date_form[date]', '2025-02-23');
        $I->seeInField('regular_play_date_form[daytime]', TimeSlotPeriodInterface::ALL);
        $I->seeInField('regular_play_date_form[venue]', 'Wichern');
        $I->seeCheckboxIsChecked('ist ein Super-Spieltermin? (nur relevant für Statistik)');

        $I->fillField('Datum', '2025-02-24');
        $I->uncheckOption('ist ein Super-Spieltermin? (nur relevant für Statistik)');
        $I->selectOption('regular_play_date_form[daytime]', TimeSlotPeriodInterface::PM);
        $I->click('Spieltermin speichern');
        $I->see('Spieltermin (regulär) wurde aktualisiert.', '.alert-success');

        $I->click('2025', '.nav-link');
        $I->see('24.02.2025');
        $I->click('Termin bearbeiten', Locator::contains('div.col-6', text: '24.02.2025'));
        $I->dontSeeCheckboxIsChecked('ist ein Super-Spieltermin? (nur relevant für Statistik)');
        $I->seeInField('regular_play_date_form[daytime]', TimeSlotPeriodInterface::PM);
        $I->seeInField('regular_play_date_form[venue]', 'Wichern');
    }
}
