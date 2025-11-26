<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;

class StatisticsCest extends AbstractCest
{
    public function byClown(FunctionalTester $I): void
    {
        Functional::$now = '2024-12-30';
        $I->loginAsClown();

        $I->click('Statistiken', '.nav');
        $I->see('monatlich', '.nav .nav-link.active');
        $I->see('Reguläre Spieltermine Dez. 2024', 'h5');

        $I->click('jährlich', '.nav');
        $I->see('jährlich', '.nav .nav-link.active');
        $I->see('Reguläre Spieltermine 2024', 'h5');


        $I->click('ewig', '.nav');
        $I->see('ewig', '.nav .nav-link.active');
        $I->see('Reguläre Spieltermine der Ewigkeit', 'h5');
    }

    public function byVenue(FunctionalTester $I): void
    {
        Functional::$now = '2024-12-30';
        $I->loginAsClown();

        $I->click('Statistiken', '.nav');
        $I->click('Nach Spielorten', '.nav');

        $I->click('jährlich', '.nav');
        $I->see('jährlich', '.nav .nav-link.active');
        $I->see('Statistik für 2024', 'h5');

        $I->click('ewig', '.nav');
        $I->see('ewig', '.nav .nav-link.active');
        $I->see('Ewige Statistik', 'h5');
    }
}
