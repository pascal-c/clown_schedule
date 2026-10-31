<?php

namespace App\Tests\Command;

use App\Entity\Month;
use App\Factory\ClownFactory;
use App\Factory\ConfigFactory;
use App\Factory\InvoiceFactory;
use App\Factory\NotificationFactory;
use App\Repository\NotificationRepository;
use App\Service\TimeService;
use App\Value\NotificationType;
use Codeception\Stub;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SendInvoiceNotificationsCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();

        $timeService = Stub::make(TimeService::class, [
            'now' => fn () => new DateTimeImmutable('2025-10-14'),
        ]);

        $container = static::getContainer();
        $container->set(TimeService::class, $timeService);
        $clownFactory = $container->get(ClownFactory::class);
        $invoiceFactory = $container->get(InvoiceFactory::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $notificationRepository = $container->get(NotificationRepository::class);
        $container->get(ConfigFactory::class)->update(featureInvoicingClowns: true);

        $clown1 = $clownFactory->create(openInvoiceEmail: true);
        $clown2 = $clownFactory->create(openInvoiceEmail: true);

        $month = Month::build('2025-09');
        $invoiceFactory->create(clown: $clown1, month: $month); // yes - create a notification for this one!
        $invoiceFactory->create(clown: $clown1, month: $month->next()); // no - this is the current month
        $invoiceFactory->create(clown: $clown2, month: $month); // no - there is already a notification for this one
        $notificationFactory->create(clown: $clown2, month: $month, type: NotificationType::INVOICE_EMAIL);

        $result = static::runCommand('app:send-invoice-notifications');

        $this->assertCommandIsSuccessful($result);

        $output = $result->getOutput();
        $this->assertStringContainsString(
            'All right! I created 1 notifications.',
            $output
        );
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailAddressContains($email, 'To', $clown1->getEmail());
        $this->assertEmailSubjectContains($email, 'Money! Money! Money!');
        $this->assertEmailSubjectNotContains($email, 'Erinnerung');

        $this->assertTrue($notificationRepository->exists(NotificationType::INVOICE_EMAIL, $clown1, $month));
        $this->assertFalse($notificationRepository->exists(NotificationType::INVOICE_EMAIL, $clown1, $month->next()));
    }
}
