<?php

namespace App\Tests\Command;

use App\Factory\PlayDateChangeRequestFactory;
use App\Value\PlayDateChangeRequestStatus;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CloseInvalidChangeRequestsCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $container = static::getContainer();
        $playDateChangeRequest = $container->get(PlayDateChangeRequestFactory::class)->create(
            status: PlayDateChangeRequestStatus::WAITING,
        );

        $command = $application->find('app:close-invalid-change-requests');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertSame(PlayDateChangeRequestStatus::CLOSED, $playDateChangeRequest->getStatus());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Good work!', $output);
    }
}
