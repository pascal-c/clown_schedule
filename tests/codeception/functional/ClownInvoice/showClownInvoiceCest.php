<?php

namespace App\Tests\Functional\ClownInvoice;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\PlayDateType;
use Codeception\Util\Locator;
use DateTimeImmutable;

class showClownInvoiceCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $currentClown = $this->clownFactory->create(email: 'emil@besen.de', password: 'secret');
        $venue1 = $this->venueFactory->create(name: 'Regulär 1');
        $venue2 = $this->venueFactory->create(name: 'Regulär 2');
        $venue3 = $this->venueFactory->create(name: 'Regulär 3'); // without fees
        $this->feeFactory->create(venue: $venue1, feeStandard: 110.0, feeAlternative: 100.00, kilometers: 50, feePerKilometer: 0.3);
        $this->feeFactory->create(venue: $venue1, validFrom: '2000-01', feeStandard: 112.0, feeAlternative: 101.00, kilometers: 100, feePerKilometer: 0.35);
        $this->feeFactory->create(venue: $venue2, feeStandard: 120.0, feeAlternative: 100.00, kilometers: 100, feePerKilometer: 0.3);

        // these playDates should be shown in december 1999
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-12-22'), venue: $venue1, playingClowns: [$currentClown]);
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-12-31'), venue: $venue2, playingClowns: [$currentClown]);
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-12-30'), title: 'Spezial', type: PlayDateType::SPECIAL, playingClowns: [$currentClown]);
        $this->playDateFactory->create(
            date: new DateTimeImmutable('1999-12-31'),
            daytime: 'pm',
            title: 'Bezahlter Zusatztermin',
            type: PlayDateType::SPECIAL,
            playingClowns: [$currentClown],
            fee: $this->feeFactory->create(feeStandard: 10.0, feeAlternative: 5.00, kilometers: 10, feePerKilometer: 0.3),
        );

        // these playDates should be shown in january 2000
        $this->playDateFactory->create(date: new DateTimeImmutable('2000-01-01'), venue: $venue1, playingClowns: [$currentClown]);
        $this->playDateFactory->create(date: new DateTimeImmutable('2000-01-31'), venue: $venue3, playingClowns: [$currentClown]);

        // these should not be shown
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-12-19'), venue: $venue1); // currentClown is not playingClown
        $this->playDateFactory->create(date: new DateTimeImmutable('1999-12-15'), title: 'Training', type: PlayDateType::TRAINING, playingClowns: [$currentClown]); // wrong type

    }

    public function show(FunctionalTester $I): void
    {
        $I->login('emil@besen.de', 'secret');

        // December 1999
        $I->amOnPage('/schedule/1999-12');
        $I->click('Rechnungsansicht');

        // first row
        $I->see('Regulär 1', '//table//tr[1]');
        $row = Locator::contains('table tbody tr', text: 'Regulär 1');
        $I->see('22.12.1999', $row);
        $I->see('110,00', $row);
        $I->see('100,00', $row);
        $I->see('15,00', $row);
        $I->seeLink('Regulär 1');

        // secondRow
        $I->see('Spezial', '//table//tr[2]');
        $row = Locator::contains('table tbody tr', text: 'Spezial');
        $I->see('30.12.1999', $row);
        $I->see('Zusatztermin', $row);
        $I->see('?', $row);
        $I->dontSeeLink('Spezial');

        // thirdRow
        $I->see('Regulär 2', '//table//tr[3]');
        $row = Locator::contains('table tbody tr', text: 'Regulär 2');
        $I->see('31.12.1999', $row);
        $I->see('120,00', $row);
        $I->see('100,00', $row);
        $I->see('30,00', $row);

        // fourthRow
        $I->see('Bezahlter Zusatztermin', '//table//tr[4]');
        $row = Locator::contains('table tbody tr', text: 'Bezahlter Zusatztermin');
        $I->see('31.12.1999', $row);
        $I->see('10,00', $row);
        $I->see('5,00', $row);
        $I->see('3,00', $row);

        // sumRow
        $I->see('Summe', '//table//tr[5]');
        $row = Locator::contains('table tbody tr', text: 'Summe');
        $I->see('Dez. 1999', $row);
        $I->see('240,00', $row);
        $I->see('205,00', $row);
        $I->see('48,00', $row);

        // what we don't see
        $I->dontSee('01.01.2000');
        $I->dontSee('19.12.1999');
        $I->dontSee('15.12.1999');

        // January 2000
        $I->amGoingTo('test next month as well');
        $I->click('>>');

        // first row
        $I->see('Regulär 1', '//table//tr[1]');
        $row = Locator::contains('table tbody tr', text: 'Regulär 1');
        $I->see('01.01.2000', $row);
        $I->see('112,00', $row);
        $I->see('101,00', $row);
        $I->see('35,00', $row);
        $I->seeLink('Regulär 1');

        // second row
        $I->see('Regulär 3', '//table//tr[2]');
        $row = Locator::contains('table tbody tr', text: 'Regulär 3');
        $I->see('31.01.2000', $row);
        $I->seeLink('Regulär 1');

        // sumRow
        $I->see('Summe', '//table//tr[3]');
        $row = Locator::contains('table tbody tr', text: 'Summe');
        $I->see('Jan. 2000', $row);
        $I->see('112,00', $row);
        $I->see('101,00', $row);
        $I->see('35,00', $row);

        // what we don't see
        $I->dontSee('19.12.1999');
    }
}
