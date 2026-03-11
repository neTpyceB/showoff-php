<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Cache\CacheStore;
use Predis\Client;

final class RedisCacheStore implements CacheStore
{
    private Client $client;

    public function __construct(string $dsn)
    {
        $this->client = new Client($dsn);
    }

    public function get(string $key): ?string
    {
        $value = $this->client->get($key);

        return is_string($value) ? $value : null;
    }

    public function set(string $key, string $value, int $ttlSeconds): void
    {
        $this->client->setex($key, $ttlSeconds, $value);
    }

    public function delete(string $key): void
    {
        $this->client->del([$key]);
    }

    public function increment(string $key): int
    {
        return (int) $this->client->incr($key);
    }
}
