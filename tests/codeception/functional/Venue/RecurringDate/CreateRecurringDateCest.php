<?php

namespace App\Tests\Functional\Venue\RecurringDate;

use App\Entity\RecurringDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class CreateRecurringDateCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venueFactory->create(
            name: 'Wichern',
            isSuper: true,
            daytimeDefault: TimeSlotPeriodInterface::AM,
            meetingTime: '08:30',
            playTimeFrom: '09:00',
            playTimeTo: '11:00',
        );
    }

    protected function before(AdminTester $I): void
    {
        Functional::$now = '2024-08-22';

        $I->loginAsAdmin();
        $I->click('Spielorte', 'nav .nav-link');
        $I->click('Wichern');
        $I->click('Spieltermine', '.nav-link');
        $I->click('Wiederkehrenden Termin anlegen');
    }

    public function createMonthlyDate(AdminTester $I): void
    {
        $this->before($I);

        $I->seeInField('Start', '2024-09-01');
        $I->seeInField('Ende', '2024-12-31');
        $I->seeInField('recurring_date_form[daytime]', TimeSlotPeriodInterface::AM);
        $I->seeCheckboxIsChecked('ist ein Super-Spieltermin? (nur relevant für Statistik)');


        $I->fillField('Start', '2025-01-01');
        $I->fillField('Ende', '2025-03-31');
        $I->selectOption('recurring_date_form[rhythm]', RecurringDate::RHYTHM_MONTHLY);
        $I->selectOption('recurring_date_form[every]', '2');
        $I->selectOption('recurring_date_form[dayOfWeek]', 'Tuesday');
        $I->selectOption('recurring_date_form[daytime]', TimeSlotPeriodInterface::ALL);

        $I->click('Wiederkehrenden Termin anlegen');
        $I->see('Wiederkehrender Termin wurde erfolgreich gespeichert. Es wurden 3 Spieltermine angelegt: 14.01.2025, 11.02.2025, 11.03.2025', '.alert-success');

        // There is no recurring date in 2024
        $I->dontSee('Wiederkehrende Termine', 'h5');

        // The recurring date was created for 2025
        $I->click('2025', '.nav-link');
        $I->see('Wiederkehrende Termine', 'h5');
        $I->see('von: 01.01.2025');
        $I->see('bis: 31.03.2025');
        $I->see('jeden 2. Dienstag im Monat');
        $I->see('ganztags');
        $I->see('Treffen: 08:30');
        $I->see('Spielzeit: 09:00-11:00');
        $I->see('ist ein Super-Termin!');
    }

    public function createWeeklyDate(AdminTester $I): void
    {
        $this->before($I);

        $I->fillField('Start', '2025-01-01');
        $I->fillField('Ende', '2025-03-31');
        $I->selectOption('recurring_date_form[rhythm]', RecurringDate::RHYTHM_WEEKLY);
        $I->selectOption('recurring_date_form[every]', '2');
        $I->selectOption('recurring_date_form[dayOfWeek]', 'Wednesday');
        $I->selectTimeOption('recurring_date_form[meetingTime]', '09:30');
        $I->selectTimeOption('recurring_date_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('recurring_date_form[playTimeTo]', '12:00');
        $I->uncheckOption('ist ein Super-Spieltermin? (nur relevant für Statistik)');

        $I->click('Wiederkehrenden Termin anlegen');
        $I->see('Wiederkehrender Termin wurde erfolgreich gespeichert. Es wurden 7 Spieltermine angelegt', '.alert-success');

        // There is no recurring date in 2024
        $I->dontSee('Wiederkehrende Termine', 'h5');

        // The recurring date was created for 2025
        $I->click('2025', '.nav-link');
        $I->see('Wiederkehrende Termine', 'h5');
        $I->see('von: 01.01.2025');
        $I->see('bis: 31.03.2025');
        $I->see('Mittwochs alle 2 Wochen');
        $I->see('vormittags');
        $I->see('Treffen: 09:30');
        $I->see('Spielzeit: 10:00-12:00');
        $I->dontSee('ist ein Super-Termin!');

        // check that rhtythm is shown in the play date details
        $I->click('2025', '.nav-link');
        $I->click('01.01.2025');
        $I->see('Mittwochs alle 2 Wochen', Locator::contains('table tr', text: 'wiederkehrend'));
    }
}
