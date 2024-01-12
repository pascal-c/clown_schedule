<?php

declare(strict_types=1);

namespace App\Tests\Step\Functional;

use App\Factory\ClownFactory;

class AdminTester extends \App\Tests\FunctionalTester
{
    public function loginAsAdmin(): void
    {
        $I = $this;
        $clown = $I->grabService(ClownFactory::class)->create(isAdmin: true, password: 'secret');
        $I->login($clown->getEmail(), 'secret');
    }
}
