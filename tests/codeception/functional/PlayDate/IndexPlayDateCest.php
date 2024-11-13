<?php

namespace App\Tests\Functional\PlayDate;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class IndexPlayDateCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        // 1984
        $this->playDateFactory->create(
            venue: $this->venueFactory->create(name: 'Seniorenheim am See'),
            date: new DateTimeImmutable('1984-07-11'),
            daytime: TimeSlotPeriodInterface::PM,
            playingClowns: [$this->clownFactory->create(name: 'Hannah'), $this->clownFactory->create(name: 'Uwe')]
        );

        // 1985
        $this->playDateFactory->create(
            venue: $this->venueFactory->create(name: 'Kinderklinik Kunterbunt'),
            date: new DateTimeImmutable('1985-02-22'),
            daytime: TimeSlotPeriodInterface::ALL,
            playingClowns: [$this->clownFactory->create(name: 'Martha'), $this->clownFactory->create(name: 'Marc')]
        );
        $this->playDateFactory->create(
            title: 'Spezialtermin',
            isSpecial: true,
            date: new DateTimeImmutable('1985-01-15'),
            daytime: TimeSlotPeriodInterface::AM,
            playingClowns: [$this->clownFactory->create(name: 'Klara'), $this->clownFactory->create(name: 'Bruno')]
        );
    }

    public function indexByYear(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/schedule');
        $I->click('Tabellarische Übersicht aller Spieltermine');

        $I->click('1984', '.nav-link');
        $I->see('Spieltermine 1984');
        $I->seeNumberOfElements('tbody tr', 1);
        $I->see('11.07.1984', Locator::contains('table tr', text: 'Seniorenheim am See'));
        $I->see('pm', Locator::contains('table tr', text: 'Seniorenheim am See'));
        $I->see('Hannah', Locator::contains('table tr', text: 'Seniorenheim am See'));
        $I->see('Uwe', Locator::contains('table tr', text: 'Seniorenheim am See'));

        $I->click('1985', '.nav-link');
        $I->see('Spieltermine 1985');
        $I->seeNumberOfElements('tbody tr', 2);
        $I->see('15.01.1985', Locator::contains('table tr', text: 'Spezialtermin'));
        $I->see('am', Locator::contains('table tr', text: 'Spezialtermin'));
        $I->see('Klara', Locator::contains('table tr', text: 'Spezialtermin'));
        $I->see('Bruno', Locator::contains('table tr', text: 'Spezialtermin'));

        $I->see('22.02.1985', Locator::contains('table tr', text: 'Kinderklinik Kunterbunt'));
        $I->see('all', Locator::contains('table tr', text: 'Kinderklinik Kunterbunt'));
        $I->see('Martha', Locator::contains('table tr', text: 'Kinderklinik Kunterbunt'));
        $I->see('Marc', Locator::contains('table tr', text: 'Kinderklinik Kunterbunt'));
    }
}
