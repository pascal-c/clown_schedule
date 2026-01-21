<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class EditBlockedClownsCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(name: 'Monique');
        $this->clownFactory->create(name: 'Pascaline');
        $this->venueFactory->create(
            name: 'Superheim',
            playingClowns: [],
            daytimeDefault: 'am',
            meetingTime: '09:30',
            playTimeFrom: '10:00',
            playTimeTo: '12:30',
            isSuper: true,
        );
    }

    public function edit(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Superheim');
        $I->see('---', Locator::contains('table tr', text: 'Gesperrte Clowns'));

        // add blocked clowns
        $I->click('Gesperrte Clowns bearbeiten');
        $I->checkMultipleOption('Gesperrte Clowns', ['Monique', 'Pascaline']);
        $I->click('speichern');
        $I->see('Monique | Pascaline', Locator::contains('table tr', text: 'Gesperrte Clowns'));
    }
}
