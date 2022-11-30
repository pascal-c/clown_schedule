<?php

namespace App\Repository;

use App\Entity\Venue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class VenueRepository extends AbstractRepository
{
    protected function getEntityName(): string
    {
        return Venue::class;
    }

    public function find(int $id): Venue
    {
        return $this->doctrineRepository->find($id);
    }

    public function all() : Array
    {
        return $this->doctrineRepository->findBy(
            [],
            ['name' => 'ASC']
        );
    }
}
