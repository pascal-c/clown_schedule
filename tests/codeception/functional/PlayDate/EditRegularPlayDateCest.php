<?php

namespace App\Tests\Functional\PlayDate;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class EditRegularPlayDateCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $venue = $this->venueFactory->create(
            name: 'Klinik Oschatz',
            meetingTime: '09:00',
            playTimeFrom: '09:30',
            playTimeTo: '12:00',
        );
        $this->playDate = $this->playDateFactory->create(
            type: PlayDateType::REGULAR,
            date: new DateTimeImmutable('2024-05-12'),
            venue: $venue,
            daytime: TimeSlotPeriodInterface::PM,
            isSuper: true,
        );
    }

    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->see('Super-Spieltermin');
        $I->click('bearbeiten', Locator::contains('table tr', text: 'Klinik Oschatz'));

        $I->see('Spieltermin (regulär) bearbeiten', 'h4');
        $I->seeInField('Datum', '2024-05-12');
        $I->seeOptionIsSelected('regular_play_date_form[daytime]', TimeSlotPeriodInterface::PM);
        $I->seeCheckboxIsChecked('ist ein Super-Spieltermin? (nur relevant für Statistik)');

        $I->fillField('Datum', '2024-05-13');
        $I->selectOption('regular_play_date_form[daytime]', TimeSlotPeriodInterface::AM);
        $I->selectTimeOption('regular_play_date_form[meetingTime]', '09:30');
        $I->selectTimeOption('regular_play_date_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('regular_play_date_form[playTimeTo]', '15:00');
        $I->uncheckOption('ist ein Super-Spieltermin? (nur relevant für Statistik)');
        $I->click('Spieltermin speichern');
        $I->see('Spieltermin (regulär) wurde aktualisiert. Sehr gut!');

        $I->see('Spieltermin (regulär) ', 'h4');
        $I->see('13.05.2024 vormittags', Locator::contains('table tr', text: 'Wann'));
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 15:00', Locator::contains('table tr', text: 'Spielzeit'));
        $I->dontSee('Super-Spieltermin');

    }
}
