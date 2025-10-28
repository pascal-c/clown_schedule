<?php

namespace App\Tests\FunctionalClownConstraints;

use App\Entity\ClownVenuePreference;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Value\Preference;
use Codeception\Util\Locator;

class clownVenuePreferencesCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $venue1 = $this->venueFactory->create(name: 'Kino 1');
        $venue2 = $this->venueFactory->create(name: 'Theater 2');
        $this->clownFactory->create(name: 'Emily', clownVenuePreferences: [
            (new ClownVenuePreference())
                ->setVenue($venue1)
                ->setPreference(Preference::BETTER),
            (new ClownVenuePreference())
                ->setVenue($venue2)
                ->setPreference(Preference::BEST),
        ]);

    }

    public function index(FunctionalTester $I): void
    {
        $I->loginAsClown('Frederico');

        $I->click('Wünsche', '.nav');
        $I->click('Spielortpräferenzen', '.nav');
        $I->click('alle anzeigen');
        $I->see('Spielortpräferenzen', '.nav .nav-link.active');

        $headerRow = Locator::contains('table thead tr', 'Spielort');
        $I->see('Emily', $headerRow.'//th[2]'); // clowns are ordered alphabetically
        $I->see('Frederi', $headerRow.'//th[3]'); // names are truncated to 7 chars
        $I->see('Ø', $headerRow.'//th[4]'); // average preference

        $venue1Row = Locator::contains('table tr', 'Kino 1');
        $I->see('+', $venue1Row.'//td[2]'); // Kino 1 Emily's preference
        $I->see('?', $venue1Row.'//td[3]'); // Kino 1 Frederico's preference
        $I->see('+', $venue1Row.'//td[4]'); // Kino 1 average preference

        $venue2Row = Locator::contains('table tr', 'Theater 2');
        $I->see('++', $venue2Row.'//td[2]'); // Kino 1 Emily's preference
        $I->see('?', $venue2Row.'//td[3]'); // Kino 1 Frederico's preference
        $I->see('+', $venue2Row.'//td[4]'); // Kino 1 average preference
    }

    public function editAndShow(FunctionalTester $I): void
    {
        $I->loginAsClown('Amy');

        $I->click('Wünsche', '.nav');
        $I->click('Spielortpräferenzen', '.nav');

        $I->see('Spielort Präferenzen Amy', 'h4');

        $I->click('Präferenzen bearbeiten');

        $I->selectOption("//fieldset[contains(., 'Kino 1')]//input", 'better');
        $I->selectOption("//fieldset[contains(., 'Theater 2')]//input", 'worse');
        $I->click('speichern');

        $I->see('Spielort Präferenzen Amy', 'h4');
        $I->see('Deine Präferenzen wurden gespeichert. Vielen Dank!', '.alert-success');
        $I->see('+', Locator::contains('table tr', 'Kino 1').'//td[2]'); // + for Kino 1
        $I->see('-', Locator::contains('table tr', 'Theater 2').'//td[2]'); // - for Theater 2
    }
}
