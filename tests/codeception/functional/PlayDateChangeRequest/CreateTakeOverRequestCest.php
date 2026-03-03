<?php

namespace App\Tests\Functional\PlayDateChangeRequest;

use App\Entity\PlayDate;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use DateTimeImmutable;

class CreateTakeOverRequestCest extends AbstractCest
{
    private PlayDate $playDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->configFactory->update(featureTeamsActive: true, teamCanAssignPlayingClowns: true);
        Functional::$now = '2025-11-20';
        $this->clownFactory->create(name: 'SomeOtherClown', email: 'some-other@clown.de', password: 'otherpass');
        $team = [];
        $team[] = $this->clownFactory->create(email: 'me@clown.de', password: 'clownpass');
        $team[] = $this->clownFactory->create(name: 'MoreOtherClown', email: 'more-other@clown.de', password: 'otherpass');
        $team[] = $this->clownFactory->create(name: 'EvenMoreOtherClown', email: 'even-more-other@clown.de', password: 'otherpass');
        $venue = $this->venueFactory->create(name: 'Paris', team: $team, teamActive: true);
        $this->playDate = $this->playDateFactory->create(playingClowns: [$team[1]], date: new DateTimeImmutable('2025-11-23'), venue: $venue);
    }

    public function takeOverRequestToOneClown(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('Clowns für diesen Spieltermin anfragen');

        $I->see('Clowns für unterbesetzten Spieltermin anfragen', 'h4');
        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'SomeOtherClown');
        $I->fillField('Hier kannst Du eine persönliche Nachricht hinzufügen', 'Hallo! Es wäre wichtig, dass das jemand übernimmt.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(1);
        $I->assertEmailAddressContains('To', 'some-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey SomeOtherClown,');
        $I->assertEmailHtmlBodyContains('Hallo! Es wäre wichtig, dass das jemand übernimmt.');
    }

    public function takeOverRequestToAnyClown(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        
        // create TakeOverRequest from dashboard
        $I->see('Termine mit fehlender Besetzung', 'h4');
        $I->click('Clowns anfragen');

        $I->see('Clowns für unterbesetzten Spieltermin anfragen', 'h4');
        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'alle Clowns');
        $I->fillField('Hier kannst Du eine persönliche Nachricht hinzufügen', 'Hallo! Es wäre wichtig, dass das jemand übernimmt.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(1);
        $I->assertEmailAddressContains('To', 'some-other@clown.de');
        $I->assertEmailAddressContains('To', 'more-other@clown.de');
        $I->assertEmailAddressContains('To', 'even-more-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey Leute,');
        $I->assertEmailHtmlBodyContains('Hallo! Es wäre wichtig, dass das jemand übernimmt.');
    }

    public function takeOverRequestToTeam(FunctionalTester $I): void
    {
        $I->login('me@clown.de', 'clownpass');
        $I->stopFollowingRedirects();
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->click('Clowns für diesen Spieltermin anfragen');

        $I->see('Clowns für unterbesetzten Spieltermin anfragen', 'h4');
        $I->selectOption('An wen soll die Anfrage gesendet werden?', 'alle im Team Paris');
        $I->fillField('Hier kannst Du eine persönliche Nachricht hinzufügen', 'Hallo! Es wäre wichtig, dass das jemand übernimmt.');
        $I->click('Abgabe-Anfrage senden');

        $I->seeEmailIsSent(3);
        $I->assertEmailAddressContains('To', 'even-more-other@clown.de');
        $I->assertEmailHtmlBodyContains('Hey EvenMoreOtherClown,');
        $I->assertEmailHtmlBodyContains('Hallo! Es wäre wichtig, dass das jemand übernimmt.');
    }

    public function tooLateToTakeOver(FunctionalTester $I): void
    {
        Functional::$now = '2025-11-21'; // must be at least 3 days before play
        $I->login('me@clown.de', 'clownpass');
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->dontSee('Clowns für diesen Spieltermin anfragen');
    }

    public function notAllowedToCreateTakeOver(FunctionalTester $I): void
    {
        $I->loginAsClown('any-other-clown');
        $I->amOnPage('/play_dates/'.$this->playDate->getId());
        $I->dontSee('Clowns für diesen Spieltermin anfragen');
    }
}
