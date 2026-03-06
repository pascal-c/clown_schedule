<?php

namespace App\Tests\Functional\PlayDateChangeRequest;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Value\PlayDateChangeRequestType;
use Codeception\Util\Locator;
use DateTimeImmutable;

class CancelTakeOverRequestCest extends AbstractCest
{
    private PlayDate $playDate;
    private PlayDateChangeRequest $playDateChangeRequest;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        Functional::$now = '2025-11-20';

        $me = $this->clownFactory->create(name: 'me', email: 'me@clown.de', password: 'clownpass', isAdmin: true);
        $other = $this->clownFactory->create(name: 'SomeOtherClown', email: 'some-other@clown.de', password: 'otherpass');
        $venue = $this->venueFactory->create(name: 'Paris');
        $this->playDate = $this->playDateFactory->create(date: new DateTimeImmutable('2025-11-23'), venue: $venue);
        $this->playDateChangeRequest = $this->playDateChangeRequestFactory->create(
            playDateToGiveOff: $this->playDate,
            requestedBy: $me,
            requestedTo: $other,
            type: PlayDateChangeRequestType::TAKE_OVER,
        );
    }

    public function cancelTakeOverRequestToOneClown(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->click('abbrechen', Locator::contains('table tr', 'vergeben an SomeOtherClown'));

        $I->see('Unterbesetzten Spieltermin doch nicht vergeben!', 'h4');
        $I->see('Du wolltest Paris an SomeOtherClown vergeben.');
        $I->click('Anfrage abbrechen');

        $I->see('Ok! Anfrage wurde erfolgreich geschlossen!', '.alert-success');
        $I->see('geschlossen', Locator::contains('table tr', 'me hat SomeOtherClown für diesen unterbesetzten Spieltermin angefragt.'));
    }

    public function notAllowedToCancelTakeOver(FunctionalTester $I): void
    {
        $I->login('some-other@clown.de', 'otherpass');
        $I->amOnPage('play_date_take-over_request/'.$this->playDateChangeRequest->getId().'/cancel');

        $I->seeResponseCodeIs(401);
    }
}
