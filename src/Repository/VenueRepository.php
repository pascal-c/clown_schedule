<?php

namespace App\Repository;

use App\Entity\Venue;

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

    public function active(): array
    {
        return $this->doctrineRepository->findBy(
            ['archived' => false],
            ['name' => 'ASC']
        );
    }

    public function archived(): array
    {
        return $this->doctrineRepository->findBy(
            ['archived' => true],
            ['name' => 'ASC']
        );
    }
}
