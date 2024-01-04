<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Mailer\PlayDateSwapRequestMailer;
use App\Value\PlayDateChangeRequestStatus;

class PlayDateChangeRequestCloseInvalidService
{
    public const ACCEPTABLE_UNTIL_PERIOD = '+3 days';
    public const CREATABLE_UNTIL_PERIOD = '+7 days';

    public function __construct(private PlayDateSwapRequestMailer $mailer, private TimeService $timeService)
    {
    }

    public function closeInvalidChangeRequests(PlayDate $playDate)
    {
        $playDateChangeRequests = array_merge($playDate->getPlayDateGiveOffRequests()->toArray(), $playDate->getPlayDateSwapRequests()->toArray());
        foreach ($playDateChangeRequests as $playDateChangeRequest) {
            $this->closeIfInvalid($playDateChangeRequest);
        }
    }

    public function closeIfInvalid(PlayDateChangeRequest $playDateChangeRequest): void
    {
        if ($playDateChangeRequest->isWaiting() && (!$playDateChangeRequest->isValid() || !$this->deadlineIsMet($playDateChangeRequest))) {
            $playDateChangeRequest->setStatus(PlayDateChangeRequestStatus::CLOSED);
            if ($playDateChangeRequest->isSwap()) {
                $this->mailer->sendSwapRequestClosedMail($playDateChangeRequest);
            }
        }
    }

    private function deadlineIsMet(PlayDateChangeRequest $playDateChangeRequest)
    {
        $deadline = $this->timeService->today()->modify(self::ACCEPTABLE_UNTIL_PERIOD);
        if ($playDateChangeRequest->getPlayDateToGiveOff()->getDate() < $deadline) {
            return false;
        }
        if ($playDateChangeRequest->isGiveOff()) {
            return true;
        }

        return $playDateChangeRequest->getPlayDateWanted()->getDate() >= $deadline;
    }
}
