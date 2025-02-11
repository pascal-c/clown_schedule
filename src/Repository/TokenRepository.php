<?php

namespace App\Repository;

use App\Entity\Token;
use App\Service\TimeService;

class TokenRepository extends AbstractRepository
{
    public function __construct(private TimeService $timeService)
    {
    }

    protected function getEntityName(): string
    {
        return Token::class;
    }

    public function find(string $token): ?Token
    {
        return $this->doctrineRepository->createQueryBuilder('token')
            ->where('token.token = :token and token.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', $this->timeService->now())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
