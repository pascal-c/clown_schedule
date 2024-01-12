<?php

namespace App\Tests\Functional\Venue;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;

class IndexVenueCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venueFactory->create(
            name: 'DRK Leipzig',
            daytimeDefault: TimeSlotPeriodInterface::PM,
            playingClowns: [$this->clownFactory->create(name: 'Anke'), $this->clownFactory->create(name: 'Pascal')],
        );
        $this->venueFactory->create(
            name: 'Wichern',
            daytimeDefault: TimeSlotPeriodInterface::ALL,
            playingClowns: [$this->clownFactory->create(name: 'Nele')],
        );
    }

    public function index(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');

        $I->amGoingTo('check the first venue');
        $I->seeLink('DRK Leipzig');
        $I->see('Anke | Pascal', Locator::contains('table tr', text: 'DRK Leipzig'));
        $I->see('nachmittags', Locator::contains('table tr', text: 'DRK Leipzig'));

        $I->amGoingTo('check the second venue');
        $I->seeLink('Wichern');
        $I->see('Nele', Locator::contains('table tr', text: 'Wichern'));
        $I->see('ganztags', Locator::contains('table tr', text: 'Wichern'));
    }
}
