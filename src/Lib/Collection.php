<?php

namespace App\Lib;

use Iterator;

class Collection implements Iterator
{
    private $position = 0;

    public function __construct(private array $list = [])
    {
    }

    public static function create(callable $callable, int $count): self
    {
        $collection = new Collection;
        for ($i=0; $i<$count; $i++) {
            $collection->push($callable());
        }

        return $collection;
    }

    public function contains(mixed $element): bool
    {
        return in_array($element, $this->list);
    }

    public function sample(): mixed
    {
        if (empty($this->list)) {
            return null;
        }

        return $this->list[array_rand($this->list)];
    }

    public function samples(int $min, ?int $max = null): array
    {
        $count = is_null($max) ? $min : rand($min, $max);
        $list = $this->list;
        shuffle($list);

        return array_slice($list, 0, $count);
    }

    public function push(mixed $element): void
    {
        $this->list[] = $element;
    }

    public function toArray(): array
    {
        return $this->list;
    }

    // Array Access
    /*public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->list[] = $value;
        } else {
            $this->list[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->list[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->list[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->list[$offset]) ? $this->list[$offset] : null;
    }*/

    // Iterator
    public function rewind(): void 
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->list[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void 
    {
        ++$this->position;
    }

    public function valid(): bool 
    {
        return isset($this->list[$this->position]);
    }
}
