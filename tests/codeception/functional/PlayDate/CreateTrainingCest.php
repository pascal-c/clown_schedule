<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class CreateTrainingCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->clownFactory->create(name: 'Thorsten', isActive: true);
        $this->clownFactory->create(name: 'Fernando', isActive: false);
    }

    public function create(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/schedule');
        $I->click('Trainingstermin anlegen');
        $I->see('Trainingstermin anlegen', 'h4');
        $I->fillField('Titel', 'Training');
        $I->fillField('Datum', '1999-12-03');
        $I->selectOption('training_form[daytime]', TimeSlotPeriodInterface::AM);
        $I->selectTimeOption('training_form[meetingTime]', '09:30');
        $I->selectTimeOption('training_form[playTimeFrom]', '10:00');
        $I->selectTimeOption('training_form[playTimeTo]', '12:00');
        $I->click('Trainingstermin speichern');
        $I->see('Trainingstermin wurde erfolgreich angelegt');

        $I->amGoingTo('test, if the new training is being showed correctly in schedule');
        $I->amOnPage('/schedule/1999-12');
        $I->see('Training', Locator::contains('.row', text: '03. Dez'));
        $I->click('Training', Locator::contains('.row', text: '03. Dez'));
        $I->see('Trainingstermin', 'h4');
        $I->see('Thorsten', Locator::contains('table tr', text: 'Spielende Clowns'));
        $I->dontSee('Fernando', Locator::contains('table tr', text: 'Spielende Clowns'));
    }
}
