<?php

namespace App\Tests\Helper;

use App\Service\TimeService;
use Codeception\Stub;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Functional extends \Codeception\Module
{
    public static string $now = 'now';

    public function _initialize()
    {
        parent::_initialize();
        $timeService = Stub::make(TimeService::class, [
            'now' => fn () => new DateTimeImmutable(Functional::$now),
        ]);

        /** @var \Codeception\Module\Symfony $symfonyModule */
        $symfonyModule = $this->getModule('Symfony');
        $container = $symfonyModule->_getContainer();

        $container->set(TimeService::class, $timeService);
        $symfonyModule->persistPermanentService(TimeService::class);
        $symfonyModule->persistPermanentService(EntityManagerInterface::class);
    }
}
