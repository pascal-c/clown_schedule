<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class EditTeamCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->configFactory->update(featureTeamsActive: true);
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

    public function toggleTeamFeature(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Superheim');
        $I->see('deaktiviert', Locator::contains('table tr', text: 'Clownsteam'));

        // activate team
        $I->click('Clownsteam bearbeiten');
        $I->checkOption('Clownsteam für diesen Spielort aktivieren');
        $I->click('speichern');
        $I->see('---', Locator::contains('table tr', text: 'Clownsteam'));

        // assign member to team
        $I->click('Clownsteam bearbeiten');
        $I->checkMultipleOption('Clownsteam', ['Monique', 'Pascaline']);
        $I->click('speichern');
        $I->see('Monique | Pascaline', Locator::contains('table tr', text: 'Clownsteam'));

        // deactivate team
        $I->click('Clownsteam bearbeiten');
        $I->uncheckOption('Clownsteam für diesen Spielort aktivieren');
        $I->click('speichern');
        $I->see('deaktiviert', Locator::contains('table tr', text: 'Clownsteam'));
    }
}
