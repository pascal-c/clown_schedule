<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Notification;
use App\Value\NotificationType;
use DateTimeImmutable;

class NotificationFactory extends AbstractFactory
{
    public function create(
        Clown $clown,
        NotificationType $type = NotificationType::INVOICE,
        ?Month $month = null,
        string $dateTime = 'now',
    ): Notification {
        $notification = new Notification()->setMonth($month);
        $notification->clown = $clown;
        $notification->type = $type;
        $notification->createdAt = new DateTimeImmutable($dateTime);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }
}
