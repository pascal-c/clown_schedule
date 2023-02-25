<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;

abstract class AbstractFactory
{
    protected Generator $generator;

    public function __construct(protected EntityManagerInterface $entityManager)
    {
        $this->generator = Factory::create('de_DE');
    }

    protected function generate(string $type, mixed $value = null): mixed
    {
        if (is_null($value)) {
            $value = $this->generator->format($type);
        }

        return $value;
    }
}
