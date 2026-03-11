<?php

declare(strict_types=1);

namespace App\Infrastructure\Lock;

use App\Concurrency\RequestLockManager;
use Predis\Client;
use Predis\Response\Status;

final class RedisRequestLockManager implements RequestLockManager
{
    private Client $client;

    /**
     * @var array<string, string>
     */
    private array $tokens = [];

    public function __construct(string $dsn)
    {
        $this->client = new Client($dsn);
    }

    public function acquire(string $key, int $ttlSeconds): bool
    {
        $token = bin2hex(random_bytes(16));
        $result = $this->client->set($key, $token, 'EX', max(1, $ttlSeconds), 'NX');

        if (!$this->setSucceeded($result)) {
            return false;
        }

        $this->tokens[$key] = $token;

        return true;
    }

    public function release(string $key): void
    {
        $token = $this->tokens[$key] ?? null;
        if ($token === null) {
            return;
        }

        $currentToken = $this->client->get($key);
        if (is_string($currentToken) && hash_equals($token, $currentToken)) {
            $this->client->del([$key]);
        }

        unset($this->tokens[$key]);
    }

    private function setSucceeded(mixed $result): bool
    {
        if (is_string($result)) {
            return strtoupper($result) === 'OK';
        }

        if ($result instanceof Status) {
            return strtoupper($result->getPayload()) === 'OK';
        }

        return $result === true;
    }
}
