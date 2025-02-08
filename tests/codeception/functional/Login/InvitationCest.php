<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Step\Functional\AdminTester;
use Codeception\Attribute\Before;

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
        $I->loginAsAdmin();
        $I->stopFollowingRedirects();
        $I->click('Clowns', 'nav');
        $I->click('Clown anlegen');
        $I->fillField('Name', 'Erika');
        $I->fillField('Email', 'erika@example.org');
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
        $I->click('Passwort setzen');

        $I->amGoingTo('login with my new password');
        $I->see('Super, Dein Zugang wurde erstellt! Du kannst Dich jetzt anmelden, Erika!');
        $I->fillField('login_form[email]', 'erika@example.org');
        $I->fillField('login_form[password]', 'abracadabra');
        $I->click('anmelden');

        $I->see('Herzlich Willkommen, Erika! Schön, dass Du da bist.');
        $I->seeCurrentUrlEquals('/dashboard');
    }
}
