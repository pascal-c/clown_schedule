<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Factory\ClownFactory;
use App\Factory\PlayDateFactory;
use App\Factory\SubstitutionFactory;
use App\Factory\VenueFactory;
use App\Tests\FunctionalTester;

abstract class AbstractCest
{
    protected ClownFactory $clownFactory;
    protected VenueFactory $venueFactory;
    protected PlayDateFactory $playDateFactory;
    protected SubstitutionFactory $substitutionFactory;

    public function _before(FunctionalTester $I): void
    {
        $this->clownFactory = $I->grabService(ClownFactory::class);
        $this->venueFactory = $I->grabService(VenueFactory::class);
        $this->playDateFactory = $I->grabService(PlayDateFactory::class);
        $this->substitutionFactory = $I->grabService(SubstitutionFactory::class);
    }
}
