<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Factory\ClownAvailabilityFactory;
use App\Factory\ClownFactory;
use App\Factory\ConfigFactory;
use App\Factory\PlayDateFactory;
use App\Factory\SubstitutionFactory;
use App\Factory\VenueFactory;
use App\Factory\FeeFactory;
use App\Factory\ScheduleFactory;
use App\Tests\FunctionalTester;

abstract class AbstractCest
{
    protected ClownFactory $clownFactory;
    protected ClownAvailabilityFactory $clownAvailabilityFactory;
    protected VenueFactory $venueFactory;
    protected FeeFactory $feeFactory;
    protected PlayDateFactory $playDateFactory;
    protected SubstitutionFactory $substitutionFactory;
    protected ScheduleFactory $scheduleFactory;
    protected ConfigFactory $configFactory;

    public function _before(FunctionalTester $I): void
    {
        $this->clownAvailabilityFactory = $I->grabService(ClownAvailabilityFactory::class);
        $this->clownFactory = $I->grabService(ClownFactory::class);
        $this->venueFactory = $I->grabService(VenueFactory::class);
        $this->feeFactory = $I->grabService(FeeFactory::class);
        $this->playDateFactory = $I->grabService(PlayDateFactory::class);
        $this->substitutionFactory = $I->grabService(SubstitutionFactory::class);
        $this->scheduleFactory = $I->grabService(ScheduleFactory::class);
        $this->configFactory = $I->grabService(ConfigFactory::class);
        $this->configFactory->update(
            feeLabel: 'Honorar Öffis',
            alternativeFeeLabel: 'Honorar PKW',
        );
    }
}
