<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Attribute\Before;
use Codeception\Util\Locator;

class InvitationCest extends AbstractCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->clownFactory->create(
            name: 'Thorsten',
            email: 'torte@example.org',
            password: 'secret',
        );
    }

    protected function invite(AdminTester $I)
    {
        Functional::$now = '2024-01-15';

        $I->loginAsAdmin();
        $I->stopFollowingRedirects();
        $I->click('Clowns', 'nav');
        $I->click('Clown anlegen');
        $I->fillField('Name', 'Erica');
        $I->fillField('Email', 'erica@example.org');
        $I->selectOption('clown_form[gender]', 'female');
        $I->checkOption('Einladungsmail senden?');
        $I->click('Clown anlegen');
        $I->seeEmailIsSent(1);
    }

    #[Before('invite')]
    public function acceptInvitation(FunctionalTester $I)
    {
        $email = $I->grabLastSentEmail();
        $I->clickLinkInEmail($email);
        $I->startFollowingRedirects();

        $I->amGoingTo('set a wrong password');
        $I->fillField('accept_invitation_form[password][password][first]', 'abracadabra');
        $I->fillField('accept_invitation_form[password][password][second]', 'abracadabr');
        $I->click('Passwort setzen');
        $I->see('Die Passwörter stimmen nicht überein.');

        $I->amGoingTo('set my new password');
        $I->fillField('accept_invitation_form[password][password][first]', 'abracadabra');
        $I->fillField('accept_invitation_form[password][password][second]', 'abracadabra');
        $I->checkOption('accept_invitation_form[privacy_policy_accepted]');
        $I->see('Ich akzeptiere diese fantastische Datenschutzerklärung.');
        $I->click('Passwort setzen');

        $I->amGoingTo('login with my new password');
        $I->see('Super, Dein Zugang wurde erstellt! Du kannst Dich jetzt anmelden, Erica!');
        $I->fillField('login_form[email]', 'erica@example.org');
        $I->fillField('login_form[password]', 'abracadabra');
        $I->click('anmelden');

        $I->see('Herzlich Willkommen, Erica! Schön, dass Du da bist.');
        $I->seeCurrentUrlEquals('/dashboard');
    }

    #[Before('acceptInvitation')]
    public function sendInvitationEmailAgainFailure(AdminTester $I)
    {
        $I->loginAsAdmin();
        $I->click('Clowns', 'nav');
        $I->click('Details bearbeiten', Locator::contains('tr', 'Erica'));
        $I->dontSee('Einladungsemail senden');
    }

    #[Before('invite')]
    public function sendInvitationEmailAgainSuccess(AdminTester $I)
    {
        $I->startFollowingRedirects();
        $I->loginAsAdmin();
        $I->stopFollowingRedirects();
        $I->click('Clowns', 'nav');
        $I->click('Details bearbeiten', Locator::contains('tr', 'Erica'));
        $I->see('Einladungsemail senden');
        $I->click('Einladungsemail senden');
        $I->seeEmailIsSent(1);
    }

    #[Before('acceptInvitation')]
    public function showPrivacyPolicyAcceptedInDetails(AdminTester $I)
    {
        $I->loginAsAdmin();
        $I->click('Clowns', 'nav');
        $I->click('Details bearbeiten', Locator::contains('tr', 'Erica'));
        $I->see('Erica hat die Datenschutzerklärung am 15.01.2024 akzeptiert.');
    }
}
