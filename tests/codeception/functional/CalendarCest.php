<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Clown;
use App\Entity\Month;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use Codeception\Util\Locator;

class CalendarCest extends AbstractCest
{
    private Clown $currentClown;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->currentClown = $this->clownFactory->create(name: 'jacques');
        $this->playDateFactory->create(Month::build('2024-11'), title: 'Other location');
        $this->playDateFactory->create(Month::build('2024-11'), title: 'My location', playingClowns: [$this->currentClown]);
    }

    public function download(FunctionalTester $I): void
    {
        Functional::$now = '2024-11-30';
        $I->login($this->currentClown->getEmail(), 'clown');

        // click to page
        $I->click('Spielplan', '.nav');
        $I->click('Kalender Export', '.nav');

        // test active navigation and headline
        $I->see('Kalender Export', '.nav .nav-link.active');
        $I->see('Kalender Download Nov. 2024', 'h5');

        // test download personal calendar
        $I->click('Jetzt herunterladen', Locator::contains('li', text: 'Persönlicher Kalender'));
        $I->seeInCurrentUrl('calendar/download?type=personal');
        $I->see('BEGIN:VCALENDAR');
        $I->see('My location');
        $I->dontSee('Other location');

        // test download full calendar
        $I->amOnPage('/calendar/form');
        $I->click('Jetzt herunterladen', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->seeInCurrentUrl('calendar/download?type=all');
        $I->see('BEGIN:VCALENDAR');
        $I->see('My location');
        $I->see('Other location');
    }

    public function subcribe(FunctionalTester $I): void
    {
        Functional::$now = '2024-11-30';
        $I->login($this->currentClown->getEmail(), 'clown');

        // click to page
        $I->click('Spielplan', '.nav');
        $I->click('Kalender Export', '.nav');

        // test active navigation and headline
        $I->see('Kalender Export', '.nav .nav-link.active');
        $I->see('Kalender Abonnement', 'h5');

        // test create subscription url for personal calendar
        $I->see('kein Link angelegt', Locator::contains('li', text: 'Persönlicher Kalender'));
        $I->click('Kalender-Abonnement-Link erzeugen', Locator::contains('li', text: 'Persönlicher Kalender'));
        $I->see('Ok, Kalender-Link wurde angelegt!', '.alert-success');
        $url = $I->grabTextFrom('span.subscription-url');

        // make sure the generated url is public and contains only personal dates
        $I->logout();
        $I->amOnPage($url);
        $I->see('BEGIN:VCALENDAR');
        $I->see('My location');
        $I->dontSee('Other location');

        // test create subscription url for full calendar
        $I->login($this->currentClown->getEmail(), 'clown');
        $I->amOnPage('/calendar/form');
        $I->see('kein Link angelegt', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->click('Kalender-Abonnement-Link erzeugen', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->see('Ok, Kalender-Link wurde angelegt!', '.alert-success');
        $url = $I->grabTextFrom("//li[contains(., 'Vollständiger Kalender')]/span[contains(@class, 'subscription-url')]");

        // make sure the generated url is public and contains all dates
        $I->logout();
        $I->amOnPage($url);
        $I->see('BEGIN:VCALENDAR');
        $I->see('My location');
        $I->see('Other location');

        // delete full subscription
        $I->login($this->currentClown->getEmail(), 'clown');
        $I->amOnPage('/calendar/form');
        $I->click('Kalender-Abonnement löschen', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->click('Kalender-Abonnement-Link jetzt löschen', Locator::contains('li', text: 'Vollständiger Kalender'));

        $I->see('kein Link angelegt', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->dontSee('kein Link angelegt', Locator::contains('li', text: 'Persönlicher Kalender'));

        // delete personal subscription
        $I->click('Kalender-Abonnement löschen', Locator::contains('li', text: 'Persönlicher Kalender'));
        $I->click('Kalender-Abonnement-Link jetzt löschen', Locator::contains('li', text: 'Persönlicher Kalender'));

        $I->see('kein Link angelegt', Locator::contains('li', text: 'Vollständiger Kalender'));
        $I->see('kein Link angelegt', Locator::contains('li', text: 'Persönlicher Kalender'));
    }
}
