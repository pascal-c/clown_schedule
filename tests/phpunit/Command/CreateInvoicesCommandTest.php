<?php

namespace App\Tests\Command;

use App\Entity\Invoice;
use App\Entity\Month;
use App\Factory\ClownFactory;
use App\Factory\PlayDateFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateInvoicesCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $clownFactory = $container->get(ClownFactory::class);

        $clown1 = $clownFactory->create();
        $clown2 = $clownFactory->create();
        $month = Month::build('2028-07');
        $container->get(PlayDateFactory::class)->create(
            month: $month,
            playingClowns: [$clown2],
        );

        $result = static::runCommand('app:create-invoices', [
            'month-string' => '2028-07',
        ]);

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString(
            'I created invoices for all confirmed and paid play dates for Month 2028-07',
            $output
        );
        $this->assertNull($clown1->getInvoiceFor($month));
        $this->assertNotNull($clown2->getInvoiceFor($month));
        $this->assertSame(Invoice::STATUS_OPEN, $clown2->getInvoiceFor($month)->getStatus());
    }
}
