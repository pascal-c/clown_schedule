<?php declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Substitution;

final class SubstitutionTest extends TestCase
{
    public function testsetDate(): void
    {
        $date = new \DateTimeImmutable('2022-11-28');
        $substitution = new Substitution;
        $substitution->setDate($date);
        $this->assertSame('2022-11', $substitution->getMonth()->getKey());
    }
}
