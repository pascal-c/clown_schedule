<?php

namespace App\Tests\Functional\PlayDateChangeRequest;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use DateTimeImmutable;

class CreateGiveOffRequestCest extends AbstractCest
{
    private PlayDate $playDate;
    private Clown $clown;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        Functional::$now = '2025-11-20';
        $team = [];
        $this->clownFactory->create(name: 'SomeOtherClown', email: 'some-other@clown.de', password: 'otherpass');
        $team[] = $this->clownFactory->create(name: 'MoreOtherClown', email: 'more-other@clown.de', password: 'otherpass');
        $team[] = $this->clownFactory->create(name: 'EvenMoreOtherClown', email: 'even-more-other@clown.de', password: 'otherpass');
        $this->clown = $this->clownFactory->create(email: 'me@clown.de', password: 'clownpass');
        $venue = $this->venueFactory->create(name: 'Paris', team: $team, teamActive: true);
        $this->playDate = $this->playDateFactory->create(playingClowns: [$this->clown], date: new DateTimeImmutable('2025-11-23'), venue: $venue);
    }

    public function giveOffToOneClown(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('Diesen Spieltermin abgeben');

        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'SomeOtherClown');
        $I->fillField('Hier kannst Du eine Nachricht an die anderen Clownis hinterlassen.', 'Ich kann da leider nicht.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(1);
        $I->assertEmailAddressContains('To', 'some-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey SomeOtherClown,');
        $I->assertEmailHtmlBodyContains('Ich kann da leider nicht.');
    }

    public function giveOffToAnyClown(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('Diesen Spieltermin abgeben');

        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'alle Clowns');
        $I->fillField('Hier kannst Du eine Nachricht an die anderen Clownis hinterlassen.', 'Ich kann da leider nicht.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(1);
        $I->assertEmailAddressContains('To', 'some-other@clown.de');
        $I->assertEmailAddressContains('To', 'more-other@clown.de');
        $I->assertEmailAddressContains('To', 'even-more-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey Leute,');
        $I->assertEmailHtmlBodyContains('Ich kann da leider nicht.');
    }

    public function giveOffToTeam(FunctionalTester $I): void
    {
        $this->configFactory->update(featureTeamsActive: true);
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('Diesen Spieltermin abgeben');

        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'alle im Team Paris');
        $I->fillField('Hier kannst Du eine Nachricht an die anderen Clownis hinterlassen.', 'Ich kann da leider nicht.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(2);
        $I->assertEmailAddressContains('To', 'even-more-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey EvenMoreOtherClown,');
        $I->assertEmailHtmlBodyContains('Ich kann da leider nicht.');
    }

    public function tooLateToGiveOff(FunctionalTester $I): void
    {
        Functional::$now = '2025-11-21'; // must be at least 3 days before play
        $I->login('me@clown.de', 'clownpass');
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->dontSee('Diesen Spieltermin abgeben');
    }

    public function wrongClownForGiveOff(FunctionalTester $I): void
    {
        $I->loginAsClown('any-other-clown');
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->dontSee('Diesen Spieltermin abgeben');
    }
}
