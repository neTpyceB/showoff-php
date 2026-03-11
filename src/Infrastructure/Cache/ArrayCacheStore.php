<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Cache\CacheStore;

final class ArrayCacheStore implements CacheStore
{
    /**
     * @var array<string, string>
     */
    private array $data = [];

    public function get(string $key): ?string
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, string $value, int $ttlSeconds): void
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function increment(string $key): int
    {
        $current = $this->data[$key] ?? '0';
        $value = is_numeric($current) ? (int) $current + 1 : 1;
        $this->data[$key] = (string) $value;

        return $value;
    }
}
