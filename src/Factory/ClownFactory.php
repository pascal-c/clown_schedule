<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Clown;
use App\Entity\UserSettings;
use App\Lib\Collection;

class ClownFactory extends AbstractFactory
{
    public function create(
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        string $password = 'clown',
        bool $isAdmin = false,
        bool $isActive = true,
        array $clownVenuePreferences = [],
        bool $scheduleCompletedEmail = true,
        bool $openInvoiceEmail = false,
        bool $openInvoiceEmailReminder = true,
    ): Clown {
        $settings = new UserSettings();
        $settings->scheduleCompletedEmail = $scheduleCompletedEmail;
        $settings->openInvoiceEmail = $openInvoiceEmail;
        $settings->openInvoiceEmailReminder = $openInvoiceEmailReminder;

        $clown = (new Clown())
            ->setName($this->generate('firstName', $name))
            ->setGender('diverse')
            ->setEmail($this->generate('safeEmail', $email))
            ->setPhone($phone)
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setIsAdmin($isAdmin)
            ->setIsActive($isActive)
            ->setSettings($settings)
        ;

        foreach ($clownVenuePreferences as $clownVenuePreference) {
            $clown->addClownVenuePreference($clownVenuePreference);
        }

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
