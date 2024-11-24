<?php

namespace App\Repository;

use App\Entity\Clown;
use App\Value\PlayDateType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ClownRepository
{
    protected EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->doctrineRepository = $entityManager->getRepository(Clown::class);
    }

    public function all(): array
    {
        return $this->doctrineRepository->findBy(
            [],
            ['isActive' => 'DESC', 'name' => 'ASC']
        );
    }

    public function allActive(): array
    {
        return $this->doctrineRepository->findBy(
            ['isActive' => true],
            ['name' => 'ASC']
        );
    }

    public function find(int $id): ?Clown
    {
        return $this->doctrineRepository->find($id);
    }

    public function findOneByEmail(string $email): ?Clown
    {
        return $this->doctrineRepository->findOneByEmail($email);
    }

    public function allWithTotalPlayDateCounts(): array
    {
        return $this->doctrineRepository->createQueryBuilder('cl')
            ->select('cl AS clown, count(pd.id) AS totalCount')
            ->leftJoin('cl.playDates', 'pd')
            ->where('pd.type = "'.PlayDateType::REGULAR->value.'"')
            ->groupBy('cl')
            ->orderBy('cl.isActive', 'DESC')
            ->addOrderBy('cl.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function allWithSuperPlayDateCounts(): array
    {
        return $this->doctrineRepository->createQueryBuilder('cl')
            ->select('cl AS clown, count(pd.id) AS superCount')
            ->leftJoin('cl.playDates', 'pd')
            ->where('pd.isSuper = 1')
            ->groupBy('cl')
            ->orderBy('cl.isActive', 'DESC')
            ->addOrderBy('cl.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
