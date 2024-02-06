<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Lib\Collection;

class ClownFactory extends AbstractFactory
{
    public function create(?string $name = null, ?string $email = null, string $password = 'clown', $isAdmin = false): Clown
    {
        $clown = (new Clown())
            ->setName($this->generate('firstName', $name))
            ->setGender('diverse')
            ->setEmail($this->generate('safeEmail', $email))
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setIsAdmin($isAdmin)
        ;

        $this->entityManager->persist($clown);
        $this->entityManager->flush();

        return $clown;
    }

    public function createList(int $min, ?int $max = null): Collection
    {
        $count = is_null($max) ? $min : rand($min, $max);
        $clowns = new Collection();
        for ($i = 0; $i < $count; ++$i) {
            $clowns->push($this->create());
        }

        return $clowns;
    }
}
