<?php

declare(strict_types=1);

namespace App\Cache;

interface CacheStore
{
    public function get(string $key): ?string;

    public function set(string $key, string $value, int $ttlSeconds): void;

    public function delete(string $key): void;

    public function increment(string $key): int;
}
