<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class ChangePasswordCest extends AbstractCest
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

    public function changePasswordSuccessfully(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'torte@example.org');
        $I->click('Passwort vergessen');
        $I->seeCurrentUrlEquals('/login');
        $I->see('Falls die Adresse richtig ist, wird ein Email mit einem Link zum Ändern Deines Passwortes an "torte@example.org" gesendet. Schau mal in Dein Email-Postfach!');
        $I->seeEmailIsSent(1);

        $email = $I->grabLastSentEmail();
        $I->clickLinkInEmail($email);

        $I->amGoingTo('set my new password');
        $I->fillField('form[password][first]', 'secret123');
        $I->fillField('form[password][second]', 'secret123');
        $I->click('Passwort ändern');

        $I->amGoingTo('login with my new password');
        $I->see('Super, Dein Passwort wurde geändert, Thorsten!');
        $I->fillField('login_form[email]', 'torte@example.org');
        $I->fillField('login_form[password]', 'secret123');
        $I->click('anmelden');

        $I->see('Herzlich Willkommen, Thorsten! Schön, dass Du da bist.');
        $I->seeCurrentUrlEquals('/dashboard');
    }

    public function changePasswordWithWrongEmail(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'kuchen@example.org');
        $I->click('Passwort vergessen');
        $I->seeCurrentUrlEquals('/login');
        $I->see('Falls die Adresse richtig ist, wird ein Email mit einem Link zum Ändern Deines Passwortes an "kuchen@example.org" gesendet. Schau mal in Dein Email-Postfach!');
        $I->seeEmailIsSent(0);
    }
}
