<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class LoginByPasswordCest extends AbstractCest
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
        $I->fillField('login_form[password]', 'secret');
        $I->click('anmelden');
        $I->seeCurrentUrlEquals('/dashboard');
        $I->see('Herzlich Willkommen, Thorsten! Schön, dass Du da bist.');
    }

    public function loginWithWrongPassword(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'torte@example.org');
        $I->fillField('login_form[password]', 'total falsches Passwort');
        $I->click('anmelden');
        $I->seeCurrentUrlEquals('/login');
        $I->seeElement('.alert-warning');
    }

    public function loginWithWrongEmail(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', 'total-falsche@email.org');
        $I->fillField('login_form[password]', 'secret');
        $I->click('anmelden');
        $I->seeCurrentUrlEquals('/login');
        $I->seeElement('.alert-warning');
    }
}
