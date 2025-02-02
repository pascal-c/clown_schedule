<?php


declare(strict_types=1);

namespace App\Service;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;

class TokenService
{
    public function __construct(private EntityManagerInterface $entityManager, private TimeService $timeService)
    {
    }

    public function deleteExpired(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Token::class, 'token')
            ->where('token.expiresAt < :now')
            ->setParameter('now', $this->timeService->now())
            ->getQuery()
            ->execute()
        ;
    }
}
