<?php

declare(strict_types=1);

namespace App\Tests\Functional\Dashboard;

use App\Entity\Clown;
use App\Entity\Month;
use App\Tests\Functional\AbstractCest;
use App\Tests\FunctionalTester;
use App\Tests\Helper\Functional;
use App\Value\ScheduleStatus;

class ShowAvailabilitiesInDashboardCest extends AbstractCest
{
    private Clown $currentClown;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->currentClown = $this->clownFactory->create(
            name: 'Hugo',
            email: 'hugo@example.org',
            password: 'secret',
        );
        $this->scheduleFactory->create(
            month: Month::build('2024-11'),
            status: ScheduleStatus::COMPLETED,
        );
        $this->scheduleFactory->create(
            month: Month::build('2024-12'),
            status: ScheduleStatus::IN_PROGRESS,
        );
    }

    public function whenSchedulesAreNotStartedYet(FunctionalTester $I): void
    {
        Functional::$now = '2024-12-30';

        $I->login(email: 'hugo@example.org', password: 'secret');

        $I->see('Fehlzeiten Jan. 2025 (noch nix eingetragen)');
        $I->see('Fehlzeiten Feb. 2025 (noch nix eingetragen)');
        $I->see('Hey Hugo, Du musst DRINGEND noch Deine Fehlzeiten für Jan. 2025 eintragen', '.alert-danger');
        $I->see('Hey Hugo, Du musst noch Deine Fehlzeiten für Feb. 2025 eintragen', '.alert-warning');
    }

    public function whenSchedulesAreNotStartedButAvailabilityAlreadyCreated(FunctionalTester $I): void
    {
        $this->clownAvailabilityFactory->create(
            clown: $this->currentClown,
            month: Month::build('2025-01'),
        );
        Functional::$now = '2024-12-30';

        $I->login(email: 'hugo@example.org', password: 'secret');

        $I->see('Fehlzeiten Jan. 2025 (schon eingetragen)');
        $I->see('Fehlzeiten Feb. 2025 (noch nix eingetragen)');
        $I->dontSee('Hey Hugo, Du musst DRINGEND noch Deine Fehlzeiten für Jan. 2025 eintragen', '.alert-danger');
        $I->see('Hey Hugo, Du musst noch Deine Fehlzeiten für Feb. 2025 eintragen', '.alert-warning');
    }

    public function whenSchedulesAreAlreadyStarted(FunctionalTester $I): void
    {
        Functional::$now = '2024-10-30';

        $I->login(email: 'hugo@example.org', password: 'secret');

        $I->see('Fehlzeiten Nov. 2024 Spielplan fertiggestellt');
        $I->see('Fehlzeiten Dez. 2024 Spielplan wird gerade erstellt');
        $I->dontSee('Hey Hugo, Du musst DRINGEND noch Deine Fehlzeiten');
        $I->dontSee('Hey Hugo, Du musst noch Deine Fehlzeiten');
    }
}
