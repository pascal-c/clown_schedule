<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Value\PlayDateChangeRequestStatus;
use App\Value\PlayDateChangeRequestType;
use DateTimeImmutable;
use Symfony\Contracts\Service\Attribute\Required;

class PlayDateChangeRequestFactory extends AbstractFactory
{
    protected PlayDateFactory $playDateFactory;
    protected ClownFactory $clownFactory;

    #[Required]
    public function _inject(PlayDateFactory $playDateFactory, ClownFactory $clownFactory)
    {
        $this->playDateFactory = $playDateFactory;
        $this->clownFactory = $clownFactory;
    }

    public function create(
        ?DateTimeImmutable $requestedAt = null,
        ?PlayDate $playDateToGiveOff = null,
        ?PlayDate $playDateWanted = null,
        ?Clown $requestedBy = null,
        ?Clown $requestedTo = null,
        PlayDateChangeRequestStatus $status = PlayDateChangeRequestStatus::WAITING,
        PlayDateChangeRequestType $type = PlayDateChangeRequestType::GIVE_OFF,
    ): PlayDateChangeRequest {
        $requestedAt ??= new DateTimeImmutable();
        $playDateToGiveOff ??= $this->playDateFactory->create();
        $playDateWanted ??= PlayDateChangeRequestType::SWAP === $type ? $this->playDateFactory->create() : null;
        $requestedBy ??= $this->clownFactory->create();
        $requestedTo ??= PlayDateChangeRequestType::SWAP === $type ? $this->clownFactory->create() : null;
        $playDateChangeRequest = (new PlayDateChangeRequest())
            ->setRequestedAt($requestedAt)
            ->setPlayDateToGiveOff($playDateToGiveOff)
            ->setPlayDateWanted($playDateWanted)
            ->setRequestedBy($requestedBy)
            ->setRequestedTo($requestedTo)
            ->setStatus($status)
            ->setType($type)
        ;

        $this->entityManager->persist($playDateChangeRequest);
        $this->entityManager->flush();

        return $playDateChangeRequest;
    }
}
