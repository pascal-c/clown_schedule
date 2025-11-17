<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;

class StatisticsCest extends AbstractCest
{
    public function x(FunctionalTester $I): void
    {
        Functional::$now = '2024-12-30';
        $I->loginAsClown();

        $I->click('Statistiken', '.nav');
        $I->see('monatlich', '.nav .nav-link.active');
        $I->see('Statistiken für Dez. 2024', 'h4');

        $I->click('jährlich', '.nav');
        $I->see('jährlich', '.nav .nav-link.active');
        $I->see('Statistik für 2024', 'h4');


        $I->click('ewig', '.nav');
        $I->see('ewig', '.nav .nav-link.active');
        $I->see('Ewige Statistik', 'h4');
    }
}
