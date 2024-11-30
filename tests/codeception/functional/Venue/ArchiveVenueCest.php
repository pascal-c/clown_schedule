<?php

namespace App\Tests\Functional\Venue;

use App\Entity\Venue;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;

class ArchiveVenueCest extends AbstractCest
{
    private Venue $venue;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venue = $this->venueFactory->create(
            name: 'Wichern',
            daytimeDefault: TimeSlotPeriodInterface::ALL,
            playingClowns: [$this->clownFactory->create(name: 'Nele')],
        );
    }

    public function archive(AdminTester $I): void
    {
        $this->playDateFactory->create(venue: $this->venue);
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Wichern');
        $I->click('bearbeiten');
        $I->see('Spielort archivieren', 'button');

        $I->click('Spielort archivieren');
        $I->see('Ok! Spielort Wichern wurde archiviert.');
        $I->dontSee('Wichern', 'table');
        $I->click('Archiv');
        $I->see('Wichern', 'table');
    }

    public function archiveNotPossibleWithoutPlayDates(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Wichern');
        $I->click('bearbeiten');
        $I->dontSee('Spielort archivieren', 'button');
    }
}
