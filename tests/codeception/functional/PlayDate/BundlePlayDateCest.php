<?php

namespace App\Tests\Functional\PlayDate;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Codeception\Util\Locator;
use DateTimeImmutable;

class BundlePlayDateCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        Functional::$now = '2036-01-10';

        $this->playDateFactory->create(
            venue: $this->venueFactory->create(name: 'Seniorenheim am See'),
            date: new DateTimeImmutable('2036-01-11'),
            daytime: TimeSlotPeriodInterface::AM,
        );
        $this->playDateFactory->create(
            type: PlayDateType::SPECIAL,
            title: 'Sondertermin 1',
            date: new DateTimeImmutable('2036-01-11'),
            daytime: TimeSlotPeriodInterface::PM,
        );
        $this->playDateFactory->create(
            type: PlayDateType::SPECIAL,
            title: 'Sondertermin 2',
            date: new DateTimeImmutable('2036-01-12'),
            daytime: TimeSlotPeriodInterface::ALL,
        );
        $this->playDateFactory->create(
            title: 'Training',
            type: PlayDateType::TRAINING,
            date: new DateTimeImmutable('2036-01-13'),
            daytime: TimeSlotPeriodInterface::AM,
        );
        $this->clownFactory->create(name: 'Claudine');
        $this->clownFactory->create(name: 'Bobo');
    }

    public function bundle(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielplan');
        $I->click('Seniorenheim am See');
        $I->dontSee('gebündelt mit');
        $I->click('Spieltermine bündeln');
        $I->dontSee('Training');
        $I->checkMultipleOption('Spieltermine', ['Sondertermin 1', 'Sondertermin 2']);
        $I->click('speichern');
        $I->see('Die Spieltermine wurden gebündelt. Gut gemacht!', '.alert-success');

        $I->see('11.01.2036 Sondertermin 1 12.01.2036 Sondertermin 2', Locator::contains('table tr', text: 'gebündelt mit'));

        // test assigning clowns to bundled play date will assign them to all play dates in the bundle
        $I->click('Zuordnung bearbeiten');
        $I->checkMultipleOption('Clowns', ['Claudine', 'Bobo']);
        $I->click('Zuordnung speichern');
        $I->see('Clowns wurden zugeordnet. Tip top!', '.alert-success');
        $I->see('Claudine | Bobo', Locator::contains('div.lh-sm', text: 'Sondertermin 1'));
        $I->see('Claudine | Bobo', Locator::contains('div.lh-sm', text: 'Sondertermin 2'));
        $I->see('Claudine | Bobo', Locator::contains('div.lh-sm', text: 'Seniorenheim am See'));
    }
}
