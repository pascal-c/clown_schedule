<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

abstract class AbstractRepository 
{
    protected EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->doctrineRepository = $entityManager->getRepository($this->getEntityName());
    }

    abstract protected function getEntityName(): string;
}
