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
            feeByPublicTransport: 145.50,
            feeByCar: 133.33,
            kilometers: 200,
            feePerKilometer: 0.31,
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
        $I->seeInField('Honorar Öffis', '145,50');
        $I->seeInField('Honorar PKW', '133,33');
        $I->seeInField('Kilometerpauschale', '0,31');
        $I->seeInField('Kilometer', '200');

        $I->amGoingTo('change some values');
        $I->uncheckMultipleOption('Verantwortliche Clowns', ['Erika', 'Elena']);
        $I->uncheckOption('ist ein Super-Spielort? (nur relevant für Statistik)');
        $I->fillField('Bemerkungen', 'Tolle Einrichtung!');
        $I->fillField('URL (für weitere Infos zur Einrichtung)', 'www.clowns-und-clowns.de');
        $I->click('Spielort speichern');

        /*
         * this does not work due to a bug in codeception with magic http methods (PUT in this case)
         *
         * $I->amGoingTo('make sure everything was changed correctly');
         * $I->see('Superheim', 'h4');
         * $I->see('Superheim', Locator::contains('table tr', text: 'Offizieller Name'));
         * $I->see('nachmittags', Locator::contains('table tr', text: 'Standard Tageszeit für Spieltermine'));
         * $I->see('09:30', Locator::contains('table tr', text: 'Treffen'));
         * $I->see('10:00 - 12:30', Locator::contains('table tr', text: 'Spielzeit'));
         * $I->see(html_entity_decode('145,50&nbsp;€'), Locator::contains('table tr', text: 'Honorar Öffis'));
         * $I->see(html_entity_decode('133,33&nbsp;€'), Locator::contains('table tr', text: 'Honorar PKW'));
         * $I->see(html_entity_decode('0,31&nbsp;€ x 200 km (Hin- und Rück) = 62,00&nbsp;€ (pro Clown) '), Locator::contains('table tr', text: 'Kilometergeld'));
         * $I->see('Tolle Einrichtung!', Locator::contains('table tr', text: 'Bemerkungen'));
         * $I->see('www.clowns-und-clowns.de', Locator::contains('table tr', text: 'Link mit weiteren Infos'));
         *
         * $I->amGoingTo('make sure especially that removing all elements from checkbox list is possible');
         * $I->dontSee('Erika', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
         * $I->dontSee('Elena', Locator::contains('table tr', text: 'Verantwortliche Clowns'));
         * $I->dontSee('ist ein Super-Spielort!');
         *
         */
    }
}
