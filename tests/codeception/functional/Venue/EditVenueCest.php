<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class EditVenueCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $erika = $this->clownFactory->create(name: 'Erika');
        $elena = $this->clownFactory->create(name: 'Elena');
        $this->venueFactory->create(
            name: 'Superheim',
            playingClowns: [$erika, $elena],
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
        $I->click('bearbeiten');
        $I->seeInField('Kurzname', 'Superheim');
        $I->seeInField('Offizieller Name', 'Superheim');

        $I->amGoingTo('change some values');
        $I->uncheckMultipleOption('Verantwortliche Clowns', ['Erika', 'Elena']);
        $I->uncheckOption('ist ein Super-Spielort? (nur relevant für Statistik)');
        $I->fillField('Bemerkungen', 'Tolle Einrichtung!');
        $I->fillField('URL (für weitere Infos zur Einrichtung)', 'www.clowns-und-clowns.de');
        $I->click('Spielort speichern');

        $I->amGoingTo('make sure everything was changed correctly');
        $I->see('Superheim', 'h4');
        $I->see('Superheim', Locator::contains('table tr', text: 'Offizieller Name'));
        $I->see('vormittags', Locator::contains('table tr', text: 'Standard Tageszeit für Spieltermine'));
        $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
        $I->see('10:00 - 12:30', Locator::contains('table tr', text: 'Spielzeit'));
        $I->see('Tolle Einrichtung!', Locator::contains('table tr', text: 'Bemerkungen'));
        $I->see('www.clowns-und-clowns.de', Locator::contains('table tr', text: 'Link mit weiteren Infos'));

        $I->amGoingTo('make sure especially that removing all elements from checkbox list is possible');
        $I->dontSee('Erika', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
        $I->dontSee('Elena', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
        $I->dontSee('ist ein Super-Spielort!');
    }
}
