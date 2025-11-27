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

        // first I see yearly statistics by type
        $I->see('jährlich', '.nav .nav-link.active');
        $I->see('Spieltermine (reguläre und Zusatztermine) nach Typ 2024', 'h5');
        $I->see('2024', '.nav .nav-link.active'); // current year is active

        // switch to status
        $I->amOnPage('/statistics/per-venue/per-year/2024?type=byStatus');
        $I->see('jährlich', '.nav .nav-link.active');
        $I->see('Spieltermine (reguläre und Zusatztermine) nach Status 2024', 'h5');
        $I->see('2024', '.nav .nav-link.active');

        // switch to infinity - we stay on status
        $I->click('ewig', '.nav');
        $I->see('ewig', '.nav .nav-link.active');
        $I->see('Spieltermine (reguläre und Zusatztermine) nach Status', 'h5');

        // switch back to type
        $I->amOnPage('/statistics/per-venue/infinity?type=byType');
        $I->see('ewig', '.nav .nav-link.active');
        $I->see('Spieltermine (reguläre und Zusatztermine) nach Typ', 'h5');
    }
}
