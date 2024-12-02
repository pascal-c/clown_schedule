<?php

namespace App\Tests\Functional\Venue\Fee;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;

class IndexVenueFeeCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $venue = $this->venueFactory->create(name: 'Spargelheim');
        $this->venueFeeFactory->create(
            venue: $venue,
            feeByPublicTransport: 140.00,
            feeByCar: 135.50,
            kilometers: 200,
            feePerKilometer: 0.3,
            kilometersFeeForAllClowns: true,
            validFrom: null,
        );
        $this->venueFeeFactory->create(
            venue: $venue,
            feeByPublicTransport: 150.0,
            feeByCar: 140.0,
            kilometers: 300,
            feePerKilometer: 0.4,
            kilometersFeeForAllClowns: false,
            validFrom: '2022-04-14',
        );
    }

    public function indexWhenLastFeeIsFromCurrentMonth(AdminTester $I): void
    {
        Functional::$now = '2022-04-15';

        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Honorare', '.nav-link');

        $I->see('Spargelheim', 'h4');
        $I->see('Honorare', 'a.nav-link.active');

        $I->see(html_entity_decode('150,00&nbsp;€'), Locator::contains('table tbody tr:first-child', text: '14.04.2022'));
        $I->see(html_entity_decode('140,00&nbsp;€'), Locator::contains('table tbody tr:first-child', text: '14.04.2022'));
        $I->see(html_entity_decode('0,40&nbsp;€ x 300 km (Hin- und Rück) = 120,00&nbsp;€ (für nur einen Clown) '), Locator::contains('table tbody tr:first-child', text: '14.04.2022'));
        $I->see('', 'table tbody tr:first-child a'); // shows an edit link

        $I->see(html_entity_decode('140,00&nbsp;€'), Locator::contains('table tbody tr:last-child', text: 'unbekannt'));
        $I->see(html_entity_decode('135,50&nbsp;€'), Locator::contains('table tbody tr:last-child', text: 'unbekannt'));
        $I->see(html_entity_decode('0,30&nbsp;€ x 200 km (Hin- und Rück) = 60,00&nbsp;€ (pro Clown) '), Locator::contains('table tbody tr:last-child', text: 'unbekannt'));
        $I->dontSee('', 'table tbody tr:last-child a'); // does not show an edit link for second row
    }

    public function indexWhenLastFeeIsOld(AdminTester $I): void
    {
        Functional::$now = '2022-05-01';

        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Honorare', '.nav-link');

        $I->dontSee('', 'table tbody tr a'); // does not show any edit link
    }
}
