<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use App\Value\PlayDateChangeRequestStatus;

class PlayDateChangeRequestRepository  extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return PlayDateChangeRequest::class;
    }

    public function find(int $id): ?PlayDateChangeRequest
    {
        return $this->doctrineRepository->find($id);
    }

    /**
     * @return array<PlayDateChangeRequest>
     */
    public function findSentRequestsWaiting(Clown $requestedBy): array
    {
        return $this->doctrineRepository->findBy([
            'status' => PlayDateChangeRequestStatus::WAITING->value,
            'requestedBy' => $requestedBy,
        ]);
    }

    /**
     * @return array<PlayDateChangeRequest>
     */
    public function findReceivedRequestsWaiting(Clown $requestedTo): array
    {
        return $this->doctrineRepository->createQueryBuilder('cr')
            ->select('cr')
            ->where('cr.status = :status AND (cr.requestedTo = :requestedTo OR cr.requestedTo IS NULL) AND cr.requestedBy != :requestedTo')
            ->orderBy('cr.requestedAt', 'DESC')
            ->setParameter('status', PlayDateChangeRequestStatus::WAITING->value)
            ->setParameter('requestedTo', $requestedTo)
            ->getQuery()
            ->getResult();
    }
}
