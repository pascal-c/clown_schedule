<?php

namespace App\Tests\Functional\Venue\Contact;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Attribute\Before;
use Codeception\Attribute\Depends;
use Codeception\Util\Locator;

class VenueContactCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->venueFactory->create(name: 'Spargelheim');
    }

    public function createSuccessfully(AdminTester $I): void
    {
        $I->loginAsAdmin();
        $I->click('Spielorte');
        $I->click('Spargelheim');
        $I->click('Kontakte');
        $I->click('Kontakt anlegen');

        $I->see('Neuen Kontakt für Spargelheim', 'h5');
        $I->fillField('Vorname', 'Steffi');
        $I->fillField('Nachname', 'Graf');
        $I->fillField('Funktion', 'Heimleiterin');
        $I->fillField('Email', 'steffi@graf.de');
        $I->fillField('Telefon', '0123456789');
        $I->click('speichern');

        $I->see('Schön! Neuen Kontakt erfolgreich angelegt!');
        $I->see('Heimleiterin', Locator::contains('table tbody tr', text: 'Steffi Graf'));
        $I->see('steffi@graf.de', Locator::contains('table tbody tr', text: 'Steffi Graf'));
        $I->see('0123456789', Locator::contains('table tbody tr', text: 'Steffi Graf'));
    }

    #[Before('createSuccessfully')]
    #[Depends('createSuccessfully')]
    public function edit(AdminTester $I): void
    {
        $I->click('bearbeiten', Locator::contains('table tbody tr:first-child', text: 'Steffi Graf'));

        $I->see('Kontakt für Spargelheim bearbeiten', 'h5');
        $I->seeInField('Vorname', 'Steffi');
        $I->seeInField('Nachname', 'Graf');
        $I->seeInField('Funktion', 'Heimleiterin');
        $I->seeInField('Email', 'steffi@graf.de');
        $I->seeInField('Telefon', '0123456789');

        $I->fillField('Vorname', 'Boris');
        $I->fillField('Nachname', 'Becker');
        $I->fillField('Funktion', 'Ergotherapeut');
        $I->fillField('Email', 'boris@becker.de');
        $I->fillField('Telefon', '4711');
        $I->click('speichern');

        $I->see('Schön! Kontakt erfolgreich aktualisiert!');
        $I->see('Ergotherapeut', Locator::contains('table tbody tr', text: 'Boris Becker'));
        $I->see('boris@becker.de', Locator::contains('table tbody tr', text: 'Boris Becker'));
        $I->see('4711', Locator::contains('table tbody tr', text: 'Boris Becker'));
    }

    #[Before('createSuccessfully')]
    #[Depends('createSuccessfully')]
    public function delete(AdminTester $I): void
    {
        $I->click('bearbeiten', Locator::contains('table tbody tr:first-child', text: 'Steffi Graf'));

        $I->see('Kontakt für Spargelheim bearbeiten', 'h5');
        $I->click('Kontakt löschen');

        $I->see('Kontakt wurde erfolgreich gelöscht.');
        $I->dontSee('Steffi Graf');
        $I->dontSee('Heimleiterin');
    }
}
