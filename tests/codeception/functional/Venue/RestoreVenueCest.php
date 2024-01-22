<?php

namespace App\Tests\Functional\Venue;

use App\Entity\Venue;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;

class RestoreVenueCest extends AbstractCest
{
    private Venue $venue;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venue = $this->venueFactory->create(
            name: 'Wichern',
            daytimeDefault: TimeSlotPeriodInterface::ALL,
            playingClowns: [$this->clownFactory->create(name: 'Nele')],
            archived: true,
        );
    }

    public function restoreWithPlayDate(AdminTester $I): void
    {
        $this->playDateFactory->create(venue: $this->venue);
        $this->restore($I);
    }

    public function restoreWithouPlayDate(AdminTester $I): void
    {
        $this->restore($I);
    }

    private function restore(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Archiv');
        $I->click('Wichern');
        $I->click('bearbeiten');
        $I->see('Spielort wiederherstellen', 'button');
        $I->click('Spielort wiederherstellen');
        $I->see('Super! Wichern ist wieder da!');
        $I->seeLink('Wichern');
        $I->click('Archiv');
        $I->dontSee('Wichern');
    }
}
