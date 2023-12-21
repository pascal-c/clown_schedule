<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Mailer\PlayDateSwapRequestMailer;
use App\Value\PlayDateChangeRequestStatus;

class PlayDateChangeRequestCloseInvalidService
{
    public function __construct(private PlayDateSwapRequestMailer $mailer)
    {}

    public function closeInvalidChangeRequests(PlayDate $playDate)
    {
        $playDateChangeRequests = array_merge($playDate->getPlayDateGiveOffRequests()->toArray(), $playDate->getPlayDateSwapRequests()->toArray());
        foreach($playDateChangeRequests as $playDateChangeRequest) {
            $this->closeIfInvalid($playDateChangeRequest);
        }
    }

    public function closeIfInvalid(PlayDateChangeRequest $playDateChangeRequest): void
    {
        if ($playDateChangeRequest->isWaiting() && !$playDateChangeRequest->isValid()) {
            $playDateChangeRequest->setStatus(PlayDateChangeRequestStatus::CLOSED);
            if ($playDateChangeRequest->isSwap()) {
                $this->mailer->sendSwapRequestClosedMail($playDateChangeRequest);
            }
        }
    }
}
