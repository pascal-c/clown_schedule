<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Clown;
use App\Entity\Notification;
use App\Entity\Month;
use App\Value\NotificationType;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TimeService $timeService,
    ) {
    }

    public function addNotification(NotificationType $type, Clown $clown, ?Month $month = null): void
    {
        $notification = new Notification();
        $notification->type = $type;
        $notification->clown = $clown;
        $notification->setMonth($month);
        $notification->createdAt = $this->timeService->now();
        $this->entityManager->persist($notification);
    }
}
