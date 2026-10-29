<?php

declare(strict_types=1);

namespace App\Tests\Service\Service\Notification;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use App\Mailer\ClownInfoMailer;
use App\Repository\InvoiceRepository;
use App\Repository\NotificationRepository;
use App\Service\ConfigService;
use App\Service\Notification\InvoiceEmailReminderNotifier;
use App\Service\Notification\NotificationService;
use App\Service\TimeService;
use App\Value\NotificationType;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InvoiceEmailReminderNotifierTest extends TestCase
{
    private InvoiceRepository&MockObject $invoiceRepository;
    private NotificationRepository&MockObject $notificationRepository;
    private ClownInfoMailer&MockObject $mailer;
    private ConfigService&MockObject $configService;
    private NotificationService&MockObject $notificationService;
    private TimeService&MockObject $timeService;
    private InvoiceEmailReminderNotifier $invoiceEmailReminderNotifier;

    public function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepository::class);
        $this->notificationRepository = $this->createMock(NotificationRepository::class);
        $this->mailer = $this->createMock(ClownInfoMailer::class);
        $this->configService = $this->createMock(ConfigService::class);
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->invoiceEmailReminderNotifier = new InvoiceEmailReminderNotifier(
            $this->invoiceRepository,
            $this->notificationRepository,
            $this->mailer,
            $this->configService,
            $this->notificationService,
            $this->timeService,
        );
    }

    #[DataProvider('dataProvider')]
    public function testNotify(
        bool $clownHasOpenInvoiceEmailSetting = true,
        string $dateString = '2025-10-15',
        bool $alreadyNotified = false,
        bool $expectedToNotify = true,
    ): void {
        $clown = new Clown()->setEmail('emil@example.com');
        $clown->getSettings()->openInvoiceEmailReminder = $clownHasOpenInvoiceEmailSetting;
        $month = Month::build('2025-09');
        $date = new DateTimeImmutable($dateString);
        $invoice = new Invoice(month: $month, clown: $clown);
        $invoices = [$invoice];
        $this->invoiceRepository
            ->expects($this->once())
            ->method('findOpen')
            ->willReturn($invoices);
        $this->notificationRepository
            ->expects($this->once())
            ->method('exists')
            ->with(NotificationType::INVOICE_EMAIL_REMINDER, $clown, $month)
            ->willReturn($alreadyNotified);
        $this->mailer
            ->expects($expectedToNotify ? $this->once() : $this->never())
            ->method('sendInvoiceNotificationReminder')
            ->with($invoice);
        $this->configService
            ->expects($this->atMost(1))
            ->method('hasFeatureInvoicingClowns')
            ->willReturn(true);
        $this->notificationService
            ->expects($expectedToNotify ? $this->once() : $this->never())
            ->method('create')
            ->with(NotificationType::INVOICE_EMAIL_REMINDER, $clown, $month);
        $this->timeService
            ->expects($this->atMost(1))
            ->method('today')
            ->willReturn($date);

        $countNotifications = $this->invoiceEmailReminderNotifier->notify();
        $this->assertSame($expectedToNotify ? 1 : 0, $countNotifications);
    }

    public static function dataProvider(): Generator
    {
        yield 'when clown does not want notifications' => [
            'clownHasOpenInvoiceEmailSetting' => false,
            'expectedToNotify' => false,
        ];

        yield 'when current date is before 15. of next month' => [
            'dateString' => '2025-10-14',
            'expectedToNotify' => false,
        ];

        yield 'when clown already has been notified' => [
            'alreadyNotified' => true,
            'expectedToNotify' => false,
        ];

        yield 'when all conditions are met' => [
            'expectedToNotify' => true,
        ];
    }
}
