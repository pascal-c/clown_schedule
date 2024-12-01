<?php

namespace App\Tests\Helper;

use App\Service\TimeService;
use Codeception\Stub;
use DateTimeImmutable;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Functional extends \Codeception\Module
{
    public static $now = 'now';

    public function _initialize()
    {
        parent::_initialize();
        $timeService = Stub::make(TimeService::class, [
            'now' => fn () => new DateTimeImmutable(Functional::$now),
        ]);

        $container = $this->getModule('Symfony')->_getContainer();

        $container->set(TimeService::class, $timeService);
        $this->getModule('Symfony')->persistService(TimeService::class);
    }
}
