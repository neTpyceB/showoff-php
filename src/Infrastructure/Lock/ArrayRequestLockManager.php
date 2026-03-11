<?php

declare(strict_types=1);

namespace App\Infrastructure\Lock;

use App\Concurrency\RequestLockManager;

final class ArrayRequestLockManager implements RequestLockManager
{
    /**
     * @var array<string, array{token: string, expiresAt: float}>
     */
    private static array $locks = [];

    /**
     * @var array<string, string>
     */
    private array $tokens = [];

    public function acquire(string $key, int $ttlSeconds): bool
    {
        $this->purgeExpired();

        if (isset(self::$locks[$key])) {
            return false;
        }

        $token = bin2hex(random_bytes(16));
        self::$locks[$key] = [
            'token' => $token,
            'expiresAt' => microtime(true) + max(1, $ttlSeconds),
        ];
        $this->tokens[$key] = $token;

        return true;
    }

    public function release(string $key): void
    {
        $this->purgeExpired();

        $token = $this->tokens[$key] ?? null;
        if ($token === null) {
            return;
        }

        $lock = self::$locks[$key] ?? null;
        if (is_array($lock) && $lock['token'] === $token) {
            unset(self::$locks[$key]);
        }

        unset($this->tokens[$key]);
    }

    private function purgeExpired(): void
    {
        $now = microtime(true);
        foreach (self::$locks as $key => $lock) {
            if ($lock['expiresAt'] <= $now) {
                unset(self::$locks[$key]);
            }
        }
    }
}
