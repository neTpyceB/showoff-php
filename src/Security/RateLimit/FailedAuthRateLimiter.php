<?php

declare(strict_types=1);

namespace App\Security\RateLimit;

use App\Cache\CacheStore;

final readonly class FailedAuthRateLimiter
{
    public function __construct(
        private CacheStore $cache,
    ) {}

    public function status(string $scope, string $subject, int $maxAttempts, int $windowSeconds): RateLimitStatus
    {
        $window = $this->loadWindow($this->key($scope, $subject), $windowSeconds);
        $blocked = $window['count'] >= $maxAttempts;

        return new RateLimitStatus(
            blocked: $blocked,
            attemptsRemaining: max(0, $maxAttempts - $window['count']),
            retryAfterSeconds: $blocked ? max(1, $window['resetAt'] - time()) : 0,
        );
    }

    public function registerFailure(string $scope, string $subject, int $maxAttempts, int $windowSeconds): RateLimitStatus
    {
        $key = $this->key($scope, $subject);
        $window = $this->loadWindow($key, $windowSeconds);
        $window['count']++;
        $this->persistWindow($key, $window, $windowSeconds);

        $blocked = $window['count'] >= $maxAttempts;

        return new RateLimitStatus(
            blocked: $blocked,
            attemptsRemaining: max(0, $maxAttempts - $window['count']),
            retryAfterSeconds: $blocked ? max(1, $window['resetAt'] - time()) : 0,
        );
    }

    public function reset(string $scope, string $subject): void
    {
        try {
            $this->cache->delete($this->key($scope, $subject));
        } catch (\Throwable) {
        }
    }

    /**
     * @return array{count: int, resetAt: int}
     */
    private function loadWindow(string $key, int $windowSeconds): array
    {
        $now = time();
        $default = [
            'count' => 0,
            'resetAt' => $now + max(1, $windowSeconds),
        ];

        try {
            $payload = $this->cache->get($key);
        } catch (\Throwable) {
            return $default;
        }

        if (!is_string($payload)) {
            return $default;
        }

        try {
            $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $default;
        }

        if (!is_array($decoded)) {
            return $default;
        }

        $count = $decoded['count'] ?? null;
        $resetAt = $decoded['resetAt'] ?? null;
        if (!is_int($count) || !is_int($resetAt) || $resetAt <= $now) {
            return $default;
        }

        return [
            'count' => max(0, $count),
            'resetAt' => $resetAt,
        ];
    }

    /**
     * @param array{count: int, resetAt: int} $window
     */
    private function persistWindow(string $key, array $window, int $windowSeconds): void
    {
        $ttlSeconds = max(1, min($window['resetAt'] - time(), $windowSeconds));

        try {
            $this->cache->set($key, json_encode($window, JSON_THROW_ON_ERROR), $ttlSeconds);
        } catch (\Throwable) {
        }
    }

    private function key(string $scope, string $subject): string
    {
        return 'security:ratelimit:' . hash('sha256', strtolower(trim($scope)) . '|' . trim($subject));
    }
}
