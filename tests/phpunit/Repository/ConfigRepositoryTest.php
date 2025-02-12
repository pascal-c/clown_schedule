<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ConfigRepositoryTest extends KernelTestCase
{
    private ConfigRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->repository = $container->get(ConfigRepository::class);
    }

    public function testFind()
    {
        $result = $this->repository->find();
        $this->assertInstanceOf(Config::class, $result);
    }
}
