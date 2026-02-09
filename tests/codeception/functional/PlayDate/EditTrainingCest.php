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

class EditTrainingCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->clownFactory->create(name: 'Thorsten', isActive: true);
        $this->clownFactory->create(name: 'Fernando', isActive: false);
        $this->playDate = $this->playDateFactory->create(
            type: PlayDateType::TRAINING,
            title: 'Training',
            date: new DateTimeImmutable('2024-05-12'),
        );
    }

    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('bearbeiten', Locator::contains('table tr', text: 'Training'));
        $I->see('Training / Team-Treffen bearbeiten', 'h4');

        $I->fillField('Titel', 'Workshop');
        $I->fillField('Datum', '2024-05-13');
        $I->selectOption('training_form[daytime]', TimeSlotPeriodInterface::ALL);
        $I->selectTimeOption('training_form[meetingTime]', '09:30');
        $I->selectTimeOption('training_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('training_form[playTimeTo]', '15:00');
        $I->click('Termin speichern');
        $I->see('Training / Team-Treffen wurde aktualisiert');

        $I->amGoingTo('test, if the new training is being showed correctly in schedule');
        $I->amOnPage('/schedule/2024-05');
        $I->see('Workshop (ganztags)', Locator::contains('.row', text: '13. Mai'));
        $I->click('Workshop (ganztags)', Locator::contains('.row', text: '13. Mai'));
        $I->see('Training / Team-Treffen', 'h4');
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 15:00', Locator::contains('table tr', text: 'Spielzeit'));

    }
}
