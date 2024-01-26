<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class LoginByEmailCest extends AbstractCest
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

    public function loginSuccessfully(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'torte@example.org');
        $I->click('per Email-Link anmelden (ohne Passwort)');
        $I->seeCurrentUrlEquals('/login');
        $I->see('Falls die Adresse richtig ist, wird ein Email mit einem Anmelde-Link an "torte@example.org" gesendet. Schau mal in Dein Email-Postfach!');
        $I->seeEmailIsSent(1);
        $email = $I->grabLastSentEmail();
        $I->clickLinkInEmail($email);
        $I->see('Herzlich Willkommen, Thorsten! Schön, dass Du da bist.');
        $I->seeCurrentUrlEquals('/dashboard');
    }

    public function loginWithWrongEmail(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'kuchen@example.org');
        $I->click('per Email-Link anmelden (ohne Passwort)');
        $I->seeCurrentUrlEquals('/login');
        $I->see('Falls die Adresse richtig ist, wird ein Email mit einem Anmelde-Link an "kuchen@example.org" gesendet. Schau mal in Dein Email-Postfach!');
        $I->seeEmailIsSent(0);
    }
}
