<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractRepository 
{
    protected EntityRepository $doctrineRepository;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->doctrineRepository = $entityManager->getRepository($this->getEntityName());
    }

    abstract protected function getEntityName(): string;
}
