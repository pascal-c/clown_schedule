<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class CreateTrainingCest extends AbstractCest
{
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
        $I->click('Training');
        $I->see('Trainingstermin', 'h4');
    }
}
