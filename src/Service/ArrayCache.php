<?php

namespace App\Service;

class ArrayCache
{
    private array $items = [];

    public function get(string $key, callable $callback): mixed
    {
        if (!array_key_exists($key, $this->items)) {
            $this->items[$key] = $callback();
        }

        return $this->items[$key];
    }

    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }
}
