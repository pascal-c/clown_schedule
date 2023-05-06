<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PlayDate;
use PHPUnit\Framework\TestCase;

final class PlayDateTest extends TestCase
{
    public function testsetDaytime(): void
    {
        $playDate = new PlayDate;
        $playDate->setDaytime('pm');
        $this->assertSame('pm', $playDate->getDaytime());
    }
}
