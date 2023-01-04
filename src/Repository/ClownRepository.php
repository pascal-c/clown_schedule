<?php

namespace App\Repository;

use App\Entity\Clown;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ClownRepository 
{
    protected EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->doctrineRepository = $entityManager->getRepository(Clown::class);
    }

    public function all(): Array
    {
        return $this->doctrineRepository->findBy(
            [],
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


//    /**
//     * @return Clown[] Returns an array of Clown objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Clown
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
