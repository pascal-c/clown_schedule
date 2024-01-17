<?php

namespace App\Tests\Functional\Login;

use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;

class RedirectToLastUriAfterLoginCest extends AbstractCest
{
    public function redirectSuccessfully(FunctionalTester $I)
    {
        $I->amOnPage('/schedule');
        $I->loginAsClown();
        $I->seeCurrentUrlEquals('/schedule');
    }
}
