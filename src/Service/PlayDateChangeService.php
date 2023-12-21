<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Value\PlayDateChangeReason;
use App\Value\PlayDateChangeRequestStatus;

class PlayDateChangeService
{
    public function __construct(private PlayDateHistoryService $playDateHistoryService)
    {}
    
    public function accept(PlayDateChangeRequest $playDateChangeRequest, Clown $requestedTo)
    {
        $playDateChangeRequest->setStatus(PlayDateChangeRequestStatus::ACCEPTED);

        $playDateChangeRequest->getPlayDateToGiveOff()->removePlayingClown($playDateChangeRequest->getRequestedBy());
        $playDateChangeRequest->getPlayDateToGiveOff()->addPlayingClown($requestedTo);
        $this->playDateHistoryService->add(
            playDate: $playDateChangeRequest->getPlayDateToGiveOff(), 
            changedBy: $playDateChangeRequest->getRequestedBy(),
            reason: $playDateChangeRequest->isSwap() ? PlayDateChangeReason::SWAP : PlayDateChangeReason::GIVE_OFF,
        );

        if ($playDateChangeRequest->isSwap()) {
            $playDateChangeRequest->getPlayDateWanted()->removePlayingClown($playDateChangeRequest->getRequestedTo());
            $playDateChangeRequest->getPlayDateWanted()->addPlayingClown($playDateChangeRequest->getRequestedBy());
            $this->playDateHistoryService->add(
                playDate: $playDateChangeRequest->getPlayDateWanted(), 
                changedBy: $playDateChangeRequest->getRequestedBy(),
                reason: PlayDateChangeReason::SWAP,
            );
        }
    }

    public function decline(PlayDateChangeRequest $playDateChangeRequest)
    {
        $playDateChangeRequest->setStatus(PlayDateChangeRequestStatus::DECLINED);
    }
}
