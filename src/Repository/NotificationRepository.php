<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\Notification;
use App\Value\NotificationType;

class NotificationRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return Notification::class;
    }

    public function exists(NotificationType $type, Clown $clown, ?Month $month = null): bool
    {
        return $this->doctrineRepository->count([
            'type' => $type,
            'clown' => $clown,
            'month' => $month?->getKey(),
        ]) > 0;
    }
}
