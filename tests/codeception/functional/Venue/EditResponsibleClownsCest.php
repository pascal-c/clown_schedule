<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class EditResponsibleClownsCest extends AbstractCest
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

    public function toggleResponsibleClownsFeatures(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Superheim');
        $I->see('---', Locator::contains('table tr', text: 'Verantwortliche Clowns'));

        // deactivate assigning responsible clown as first clown
        $I->click('Verantwortliche Clowns bearbeiten');
        $I->uncheckOption('"Verantwortlichen Clown als ersten Clown" aktivieren');
        $I->click('speichern');
        $I->see('deaktiviert', Locator::contains('table tr', text: 'Verantwortliche Clowns'));

        // reactivate assigning responsible clown as first clown
        $I->click('Verantwortliche Clowns bearbeiten');
        $I->checkOption('"Verantwortlichen Clown als ersten Clown" aktivieren');
        $I->checkMultipleOption('Verantwortliche Clowns', ['Monique', 'Pascaline']);
        $I->click('speichern');
        $I->see('Monique | Pascaline', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
    }
}
