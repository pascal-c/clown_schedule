<?php

namespace App\Tests\Functional\PlayDate;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Util\Locator;
use DateTimeImmutable;

class DeletePlayDateCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $date = new DateTimeImmutable('2036-01-15');

        $venue = $this->venueFactory->create(
            name: 'Seniorenheim am See',
        );
        $this->playDate = $this->playDateFactory->create(
            venue: $venue,
            date: $date,
        );
    }

    public function delete(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());

        $I->click('Spieltermin löschen', 'a[title="Spieltermin löschen"]');
        $I->see('Der Spieltermin wird dann endgültig gelöscht und kann nicht wiederhergestellt werden.', '.modal-body');
        $I->click('Diesen Spieltermin löschen', '.modal-footer');
        $I->see('Spieltermin wurde gelöscht.', '.alert-success');

        $I->amOnPage('/schedule/2036-01');
        $I->dontSee('Seniorenheim am See');
    }

    public function deleteRecurring(AdminTester $I): void
    {
        $playDateBefore = $this->playDateFactory->create(
            date: new DateTimeImmutable('2036-01-01'),
            venue: $this->playDate->getVenue(),
        );
        $playDateAfter = $this->playDateFactory->create(
            date: new DateTimeImmutable('2036-01-29'),
            venue: $this->playDate->getVenue(),
        );
        $this->recurringDateFactory->create(
            rhythm: 'weekly',
            every: 2,
            dayOfWeek: 'Thursday',
            venue: $this->playDate->getVenue(),
            playDates: [$playDateBefore, $this->playDate, $playDateAfter],
        );

        $I->loginAsAdmin();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());

        $I->click('Spieltermin löschen', 'a[title="Spieltermin löschen"]');
        $I->see('Der Spieltermin wird dann endgültig gelöscht und kann nicht wiederhergestellt werden.', '.modal-body');
        $I->see('Diesen Spieltermin löschen', '.modal-footer button');
        $I->click('Diesen Termin und alle künftigen Wiederholungen löschen', '.modal-footer');
        $I->see('Es wurden 2 Spieltermine gelöscht.', '.alert-success');

        $I->amOnPage('/schedule/2036-01');
        $I->see('Seniorenheim am See', Locator::contains('div.row', text: '01. Jan')); // the play date before is still there
        $I->dontSee('Seniorenheim am See', Locator::contains('div.row', text: '15. Jan')); // the play date itself is gone
        $I->dontSee('Seniorenheim am See', Locator::contains('div.row', text: '29. Jan')); // the play date after is also gone
    }
}
