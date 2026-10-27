<?php

namespace App\Tests\Command;

use App\Entity\Clown;
use App\Entity\Month;
use App\Factory\ClownFactory;
use App\Factory\InvoiceFactory;
use App\Factory\NotificationFactory;
use App\Repository\NotificationRepository;
use App\Service\TimeService;
use App\Value\NotificationType;
use Codeception\Stub;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SendInvoiceNotificationsReminderCommandTest extends KernelTestCase
{
    private NotificationRepository $notificationRepository;
    private string $currentDateTime;
    private Month $month;
    private Clown $clown1;
    private Clown $clown2;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $timeService = Stub::make(TimeService::class, [
            'now' => fn () => new DateTimeImmutable($this->currentDateTime),
        ]);

        $container = static::getContainer();
        $container->set(TimeService::class, $timeService);
        $clownFactory = $container->get(ClownFactory::class);
        $invoiceFactory = $container->get(InvoiceFactory::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $this->notificationRepository = $container->get(NotificationRepository::class);

        $this->clown1 = $clownFactory->create();
        $this->clown2 = $clownFactory->create();

        $this->month = Month::build('2025-09');
        $invoiceFactory->create(clown: $this->clown1, month: $this->month); // yes - create a notification for this one!
        $invoiceFactory->create(clown: $this->clown1, month: $this->month->next()); // no - this is the current month
        $invoiceFactory->create(clown: $this->clown2, month: $this->month); // no - there is already a notification for this one
        $notificationFactory->create(clown: $this->clown2, month: $this->month, type: NotificationType::INVOICE_REMINDER);
    }

    public function testExecuteOn15thOfNextMonth(): void
    {
        $this->currentDateTime = '2025-10-15';

        $result = static::runCommand('app:send-invoice-notifications-reminder');

        $this->assertCommandIsSuccessful($result);

        $this->assertStringContainsString(
            'All right! I created 1 invoice reminder notifications.',
            $result->getOutput()
        );

        // check the email
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailAddressContains($email, 'To', $this->clown1->getEmail());
        $this->assertEmailSubjectContains($email, 'Erinnerung: Money! Money! Money!');

        // check created notification
        $this->assertTrue($this->notificationRepository->exists(
            NotificationType::INVOICE_REMINDER, 
            $this->clown1, 
            $this->month,
        ));
        $this->assertFalse($this->notificationRepository->exists(
            NotificationType::INVOICE_REMINDER, 
            $this->clown1, 
            $this->month->next(),
        ));
    }

    public function testExecuteOn14thOfNextMonth(): void
    {
        $this->currentDateTime = '2025-10-14';

        $result = static::runCommand('app:send-invoice-notifications-reminder');

        $this->assertCommandIsSuccessful($result);

        $this->assertStringContainsString(
            'All right! I created 0 invoice reminder notifications.',
            $result->getOutput()
        );
        $this->assertEmailCount(0);

        $this->assertFalse($this->notificationRepository->exists(
            NotificationType::INVOICE_REMINDER, 
            $this->clown1, 
            $this->month,
        ));
        $this->assertFalse($this->notificationRepository->exists(
            NotificationType::INVOICE_REMINDER, 
            $this->clown1, 
            $this->month->next(),
        ));
    }
}
