<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contact;
use App\Entity\Venue;
use App\Lib\Collection;

class ContactFactory extends AbstractFactory
{
    public function create(
        Venue $venue,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $function = null,
    ): Contact {
        $contact = (new Contact())
            ->setFirstName($this->generate('firstName', $firstName))
            ->setLastName($this->generate('lastName', $lastName))
            ->setEmail($this->generate('safeEmail', $email))
            ->setPhone($this->generate('phoneNumber', $phone))
            ->setFunction($function ?? $this->functionOptions()->sample())
        ;

        $venue->addContact($contact);
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    public function createList(Venue $venue, int $min, ?int $max = null): Collection
    {
        $count = is_null($max) ? $min : rand($min, $max);
        $contacts = new Collection();
        for ($i = 0; $i < $count; ++$i) {
            $contacts->push($this->create($venue));
        }

        return $contacts;
    }

    private function functionOptions(): Collection
    {
        return new Collection([
            'Ergotherapie',
            'Heimleitung',
            'Pflegedienstleitung',
            'Ergo',
            'sozialer Dienst',
            'soziale Betreuung',
        ]);
    }
}
