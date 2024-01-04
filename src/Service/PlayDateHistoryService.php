<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateHistory;
use App\Value\PlayDateChangeReason;

class PlayDateHistoryService
{
    public function __construct(
        private TimeService $timeService,
        private PlayDateChangeRequestCloseInvalidService $closeInvalidService,
    ) {
    }

    public function add(PlayDate $playDate, ?Clown $changedBy, PlayDateChangeReason $reason): void
    {
        $this->closeInvalidService->closeInvalidChangeRequests($playDate);

        $playDateHistoryEntry = (new PlayDateHistory())
            ->setChangedAt($this->timeService->now())
            ->setChangedBy($changedBy)
            ->setReason($reason);

        foreach ($playDate->getPlayingClowns() as $clown) {
            $playDateHistoryEntry->addPlayingClown($clown);
        }

        $playDate->addPlayDateHistoryEntry($playDateHistoryEntry);
    }
}
