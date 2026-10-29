<?php

declare(strict_types=1);

namespace App\Tests\Service\Service\Notification;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Notification;
use App\Service\Notification\NotificationService;
use App\Service\TimeService;
use App\Value\NotificationType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private TimeService&MockObject $timeService;
    private NotificationService $notificationService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->timeService = $this->createMock(TimeService::class);
        $this->notificationService = new NotificationService($this->entityManager, $this->timeService);
    }

    public function testcreate(): void
    {
        $month = new Month(new DateTimeImmutable('2023-01'));
        $clown = new Clown();
        $dateTime = new DateTimeImmutable('2023-01-22 15:34:33');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(
                function (Notification $notification) use ($clown, $month, $dateTime) {
                    $this->assertSame(NotificationType::INVOICE_EMAIL_REMINDER, $notification->type);
                    $this->assertSame($clown, $notification->clown);
                    $this->assertEquals($month, $notification->getMonth());
                    $this->assertSame($dateTime, $notification->createdAt);
                }
            );
        $this->timeService->expects($this->once())
            ->method('now')
            ->willReturn($dateTime);

        $this->notificationService->create(NotificationType::INVOICE_EMAIL_REMINDER, $clown, $month);
    }
}
