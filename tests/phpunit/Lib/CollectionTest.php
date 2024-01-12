<?php

declare(strict_types=1);

namespace App\Tests\Lib;

use App\Lib\Collection;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function testSample(): void
    {
        $collection = new Collection();
        $this->assertSame(null, $collection->sample());

        $collection = new Collection(['element']);
        $this->assertSame('element', $collection->sample());

        $collection = new Collection(['element1', 'element2', 'element3']);
        $this->assertContains($collection->sample(), $collection);
    }

    public function testSamples(): void
    {
        $collection = new Collection();
        $this->assertSame([], $collection->samples(3));

        $collection = new Collection([1, 2, 3, 4, 5]);
        $result = $collection->samples(1, 2);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertLessThanOrEqual(2, count($result));

        foreach ($result as $element) {
            $this->assertContains($element, $collection);
        }
    }

    public function testPush(): void
    {
        $collection = new Collection(['element1']);
        $collection->push('element2');
        $this->assertSame(['element1', 'element2'], $collection->toArray());
    }
}
